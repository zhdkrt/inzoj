<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\BodyLog;
use App\Models\User;
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

        $calculate_bmr = 10 * $user->current_weight + 6.25 * $user->height - 5 * $user->age;
        $bmr = round($user->gender == 'male' ? $calculate_bmr + 5 : $calculate_bmr - 161, 0);

        $height_in_meters = $user->height / 100;
        $imt = round($user->current_weight / (pow($height_in_meters, 2)), 1);
        switch($imt) {
            case $imt < 18.5:
                $imt_classification = 'дефицит массы тела';
                break;
            case $imt >= 18.5 && $imt <= 24.9:
                $imt_classification = 'норма';
                break;
            case $imt >= 25 && $imt <= 29.9:
                $imt_classification = 'избыточная масса';
                break;
            case $imt >= 30:
                $imt_classification = 'ожирение';
                break;
        }

        $activity_level_coefficient = [
            'low' => 1.2,
            'medium' => 1.55,
            'high' => 1.725,
            'expert' => 1.9,
        ];
        $calories_norm = round($bmr * $activity_level_coefficient[$user->activity_level], 0);

        $calculated_data = [
            'bmr' => $bmr,
            'imt' => $imt,
            'imt_classification' => $imt_classification,
            'calories_norm' => $calories_norm
        ];
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'user' => $user,
                'fields' => $this->fields,
                'editing' => false,
                'calculated_data' => $calculated_data
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
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non-existent field'
                ], 400);
            }        }
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

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Данные обновлены',
                '_csrf_token' => csrf_token()
            ]);
        }
        
        return redirect()->route('userData');
    }
}