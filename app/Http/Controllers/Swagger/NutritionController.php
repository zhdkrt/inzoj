<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;

/**
 * @OA\Tag(
 *     name="Nutrition",
 *     description="Mifflin-St Jeor nutrition plan, daily/weekly KBJU+water+steps limits, weekly meal regime and BMI. Free: Mifflin. Premium: extended calculator."
 * )
 *
 * @OA\Get(
 *     path="/api/profile/nutrition",
 *     summary="Nutrition plan and BMI",
 *     description="Returns stored daily limits and a calculated plan: Mifflin-St Jeor (free) or Mifflin+Harris-Benedict blend (premium), daily and weekly KBJU/water/steps, 7-day regime with product recommendations filtered by diet and allergies, and BMI with Broca, Lorentz, waist/hip/neck/chest when logs exist.",
 *     operationId="getNutritionPlan",
 *     tags={"Nutrition"},
 *     security={{"userSanctumToken": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Plan. If profile is incomplete, plan.ready is false and limits are null.",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="is_premium", type="boolean", example=false),
 *             @OA\Property(
 *                 property="stored_limits",
 *                 type="object",
 *                 @OA\Property(property="calories", type="integer", nullable=true, example=1800),
 *                 @OA\Property(property="proteins", type="number", nullable=true, example=135.0),
 *                 @OA\Property(property="fats", type="number", nullable=true, example=50.0),
 *                 @OA\Property(property="carbs", type="number", nullable=true, example=180.0),
 *                 @OA\Property(property="water", type="number", nullable=true, example=2.3),
 *                 @OA\Property(property="steps", type="integer", nullable=true, example=8000)
 *             ),
 *             @OA\Property(
 *                 property="plan",
 *                 type="object",
 *                 @OA\Property(property="ready", type="boolean", example=true),
 *                 @OA\Property(property="goal", type="string", example="lose_weight"),
 *                 @OA\Property(
 *                     property="bmi",
 *                     type="object",
 *                     @OA\Property(property="value", type="number", example=24.5),
 *                     @OA\Property(property="classification", type="string", example="норма"),
 *                     @OA\Property(property="ideal_weight_broca_kg", type="integer", example=75),
 *                     @OA\Property(property="ideal_weight_lorentz_kg", type="number", example=70.0),
 *                     @OA\Property(property="parameters_used", type="array", @OA\Items(type="string"), example={"weight", "height", "age", "gender"}),
 *                     @OA\Property(property="optional", type="object", description="waist, hips, neck, chest, WHtR, WHR, Navy body fat, Pignet when measurements exist")
 *                 ),
 *                 @OA\Property(
 *                     property="calculator",
 *                     type="object",
 *                     @OA\Property(property="used", type="string", enum={"mifflin_st_jeor", "extended"}),
 *                     @OA\Property(property="free", type="object", description="Mifflin-St Jeor BMR, TDEE, calories_target"),
 *                     @OA\Property(property="extended", type="object", nullable=true, description="null without premium")
 *                 ),
 *                 @OA\Property(
 *                     property="limits",
 *                     type="object",
 *                     @OA\Property(property="daily", type="object"),
 *                     @OA\Property(property="weekly", type="object", description="daily × 7")
 *                 ),
 *                 @OA\Property(property="weekly_regime", type="object", description="KBJU proportions, meal split 25/35/30/10, 7 days. Each meal has items with grams and KBJU; day totals ≈ daily limit. Products rotate by user and weekday, filtered by diet/allergies."),
 *                 @OA\Property(property="product_recommendations", type="array", @OA\Items(type="object")),
 *                 @OA\Property(property="health", type="object")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 * @OA\Post(
 *     path="/api/profile/nutrition/recalculate",
 *     summary="Recalculate and save nutrition targets",
 *     description="Writes daily calories, proteins, fats, carbs, water and steps to the user. Uses Mifflin-St Jeor for free users and the extended calculator for premium.",
 *     operationId="recalculateNutritionPlan",
 *     tags={"Nutrition"},
 *     security={{"userSanctumToken": {}}},
 *     @OA\Response(response=200, description="Targets updated"),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(
 *         response=422,
 *         description="Profile incomplete (need weight, height, age, gender, activity_level)"
 *     )
 * )
 */
class NutritionController extends Controller
{
    //
}
