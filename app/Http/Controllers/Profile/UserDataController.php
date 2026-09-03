<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\BodyLog;
use App\Services\NutritionCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDataController extends Controller
{
    private $fields = [
        'current_weight' => 'Текущий вес',
        'height' => 'Рост',
        'age' => 'Возраст',
        'gender' => 'Пол'
    ];

    public function show(Request $request)
    {
        $user = Auth::user();
        $plan = NutritionCalculator::build($user);
        $calculated_data = $this->legacyCalculatedData($plan);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'user' => $user,
                'fields' => $this->fields,
                'editing' => false,
                'calculated_data' => $calculated_data,
                'plan' => $plan,
            ]);
        }
        return view('profile.userData', [
            'user' => $user,
            'fields' => $this->fields,
            'editing' => false,
            'calculated_data' => $calculated_data
        ]);
    }

    public function edit(Request $request)
    {
        $user = Auth::user();
        $field = $request->get('field');

        if (!array_key_exists($field, $this->fields)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non-existent field'
                ], 400);
            }
            return redirect()->route('userData');
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

        return view('profile.userData', [
            'user' => $user,
            'fields' => $this->fields,
            'editing' => true,
            'editingField' => $field
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $field = $request->get('field');

        if (!array_key_exists($field, $this->fields)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non-existent field'
                ], 400);
            }
            return redirect()->route('userData');
        }

        $rules = [];

        switch ($field) {
            case 'current_weight':
                $rules['value'] = 'required|numeric|min:30|max:300';
                break;
            case 'height':
                $rules['value'] = 'required|integer|min:100|max:220';
                break;
            case 'age':
                $rules['value'] = 'required|integer|min:18|max:100';
                break;
            case 'gender':
                $rules['value'] = 'required|in:male,female';
                break;
        }
        $validated = $request->validate($rules);

        $user->update([$field => $validated['value']]);

        if ($field === 'current_weight') {
            BodyLog::record($user, BodyLog::TYPE_WEIGHT, $validated['value']);
        }

        NutritionCalculator::applyToUser($user->fresh());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Данные обновлены',
                '_csrf_token' => csrf_token()
            ]);
        }

        return redirect()->route('userData');
    }

    private function legacyCalculatedData(array $plan): array
    {
        return [
            'bmr' => $plan['calculator']['free']['bmr'] ?? null,
            'imt' => $plan['bmi']['value'] ?? null,
            'imt_classification' => $plan['bmi']['classification'] ?? null,
            'calories_norm' => $plan['limits']['daily']['calories'] ?? null,
        ];
    }
}
