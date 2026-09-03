<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Services\NutritionCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NutritionController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();
        $plan = NutritionCalculator::build($user);

        return response()->json([
            'success' => true,
            'is_premium' => $user->isPremiumActive(),
            'stored_limits' => [
                'calories' => $user->calories,
                'proteins' => $user->proteins,
                'fats' => $user->fats,
                'carbs' => $user->carbs,
                'water' => $user->water,
                'steps' => $user->steps,
            ],
            'plan' => $plan,
        ]);
    }

    public function recalculate(Request $request)
    {
        $user = Auth::user();
        if (!NutritionCalculator::canCalculate($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Fill weight, height, age, gender and activity level first',
                'plan' => NutritionCalculator::build($user),
            ], 422);
        }

        $user = NutritionCalculator::applyToUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Nutrition targets updated',
            'is_premium' => $user->isPremiumActive(),
            'stored_limits' => [
                'calories' => $user->calories,
                'proteins' => $user->proteins,
                'fats' => $user->fats,
                'carbs' => $user->carbs,
                'water' => $user->water,
                'steps' => $user->steps,
            ],
            'plan' => NutritionCalculator::build($user),
        ]);
    }
}
