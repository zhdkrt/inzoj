<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\BodyLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class TargetsController extends Controller
{
    private $fields = [
        'goal' => 'Цель',
        'current_weight' => 'Текущий вес',
        'target_weight' => 'Желаемый вес',
        'activity_level' => 'Уровень активности',
        'calories' => 'Цель по калориям',
        'water' => 'Цель по воде',
        'steps' => 'Шаги(цель)'
    ];

    public function show(Request $request)
    {
        $user_id = $request->get('user_id');
        $user = User::find($user_id) ?? Auth::user();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'user' => $user,
                'fields' => $this->fields,
                'editing' => false,
            ]);
        }

        return view('profile.targets', [
            'user' => $user,
            'fields' => $this->fields,
            'editing' => false
        ]);
    }

    public function edit(Request $request)
    {
        $user_id = $request->get('user_id');
        $user = User::find($user_id) ?? Auth::user();
        $field = $request->get('field');

        if (!array_key_exists($field, $this->fields)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non-existent field'
                ], 400);
            }
            return redirect()->route('targets');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'user' => $user,
                'fields' => $this->fields,
                'editing' => true,
                'editingField' => $field,
            ]);
        }

        return view('profile.targets', [
            'user' => $user,
            'fields' => $this->fields,
            'editing' => true,
            'editingField' => $field
        ]);
    }


    public function update(Request $request)
    {
        $user_id = $request->user_id ?? null;
        $user = User::find($user_id) ?? Auth::user();
        $field = $request->get('field') ?? 'water';

        if (!array_key_exists($field, $this->fields)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non-existent field'
                ], 400);
            }
            return redirect()->route('targets');
        }

        $rules = [];
        
        switch ($field) {
            case 'goal':
                $rules['value'] = 'required|in:lose_weight,gain_muscle,maintain,other';
                break;
            case 'target_weight':
            case 'current_weight':
                $rules['value'] = 'required|numeric|min:30|max:300';
                break;
            case 'activity_level':
                $rules['value'] = 'required|in:low,medium,high,expert';
                break;
            case 'calories':
                $rules['value'] = 'required|integer|min:1000|max:5000';
                break;
            case 'water':
                $rules['value'] = 'required|decimal:1,1|min:1.0|max:3.0';
                break;
            case 'steps':
                $rules['value'] = 'required|integer|min:1000|max:40000';
                break;
        }
        $validated = $request->validate($rules);

        $user->update([$field => $validated['value']]);

        if ($field === 'current_weight') {
            BodyLog::record($user, BodyLog::TYPE_WEIGHT, $validated['value']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Данные обновлены',
                '_csrf_token' => csrf_token()
            ]);
        }
        return redirect()->route('targets');
    }

}
