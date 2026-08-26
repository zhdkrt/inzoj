<?php

namespace App\Http\Controllers;

use App\Models\BodyLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionnaireController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();
        $step = $request->get('step', 'name');
        
        $stepFieldMap = [
            'name' => 'name',
            'goal' => 'goal', 
            'weight' => 'current_weight',
            'height' => 'height',
            'age' => 'age',
            'activity' => 'activity_level'
        ];

        $steps = array_keys($stepFieldMap);

        foreach ($steps as $stepName) {
            $fieldName = $stepFieldMap[$stepName];
            if (empty($user->{$fieldName})) {
                $step = $stepName;
                break;
            }
        }

        $data = [
            'name' => $user->name,
            'goal' => $user->goal,
            'current_weight' => $user->current_weight,
            'target_weight' => $user->target_weight,
            'height' => $user->height,
            'age' => $user->age,
            'activity_level' => $user->activity_level,
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'step' => $step,
                'data' => $data,
                'steps' => $steps
            ]);
        }        

        return view('profile.questionnaire', compact('step', 'data'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $currentStep = $request->get('step', 'name');
        $action = $request->get('action', 'next');

        $rules = [];
        
        if ($currentStep === 'name') {
            $rules['name'] = 'required|string|max:255';
        } elseif ($currentStep === 'goal') {
            $rules['goal'] = 'required|in:lose_weight,gain_muscle,maintain,other';
        } elseif ($currentStep === 'weight') {
            $rules['current_weight'] = 'required|integer|min:30|max:300';
            $rules['target_weight'] = 'nullable|integer|min:30|max:300';
        } elseif ($currentStep === 'height') {
            $rules['height'] = 'required|integer|min:100|max:220';
        } elseif ($currentStep === 'age') {
            $rules['age'] = 'required|integer|min:18|max:100';
        } elseif ($currentStep === 'activity') {
            $rules['activity_level'] = 'required|in:low,medium,high,expert';
        }

        $validated = $request->validate($rules);

        $user->update($validated);

        if (array_key_exists('current_weight', $validated)) {
            BodyLog::record($user, BodyLog::TYPE_WEIGHT, $validated['current_weight']);
        }

        // Определяем следующий шаг
        $steps = ['name', 'goal', 'weight', 'height', 'age', 'activity'];
        $currentIndex = array_search($currentStep, $steps);

        if ($action === 'prev' && $currentIndex > 0) {
            $nextStep = $steps[$currentIndex - 1];
        } elseif ($action === 'next' && $currentIndex < count($steps) - 1) {
            $nextStep = $steps[$currentIndex + 1];
        } else {
            $nextStep = $currentStep;
        }

        if ($request->expectsJson()) {
            $isComplete = ($action === 'next' && $currentStep === 'activity');
            
            return response()->json([
                'success' => true,
                'message' => 'Step completed successfully',
                'next_step' => $isComplete ? null : $nextStep,
                'is_complete' => $isComplete,
                'redirect_to' => $isComplete ? '/api/diary' : null,
                'user' => $user
            ]);
        }

        if ($action === 'next' && $currentStep === 'activity') {
            return redirect()->route('diary');
        }

        return redirect()->route('questionnaire', ['step' => $nextStep]);
    }
}
