<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Diary - Recipes",
 *     description="Recipe management in diary meals"
 * )
 * 
 * @OA\Get(
 *     path="/api/diary/meal/recepie",
 *     summary="Get recipe details",
 *     description="Returns recipe information for editing or adding to meal",
 *     operationId="getRecepie",
 *     tags={"Diary - Recipes"},
 *     security={{"userSanctumToken": {}}},
 *     
 *     @OA\Parameter(
 *         name="diary_note_id",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="integer", example=33),
 *         description="Diary note ID from `GET /api/diary` response"
 *     ),
 *     
 *     @OA\Parameter(
 *         name="recepie_id",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="integer", example=2),
 *         description="Recipe ID"
 *     ),
 *     
 *     @OA\Parameter(
 *         name="meal_type",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string", enum={"breakfast", "lunch", "dinner", "snack"}),
 *         description="Type of meal (required when adding new recipe)"
 *     ),
 * 
 *     @OA\Parameter(
 *         name="user_meal_id",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="integer", example=45),
 *         description="User meal ID (for editing existing meal)"
 *     ),
 *          
 *     @OA\Response(
 *         response=200,
 *         description="Recipe retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="recepie", ref="#/components/schemas/Recepie"),
 *             @OA\Property(
 *                 property="ingredients",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/RecepieIngredient")
 *             ),
 *             @OA\Property(property="amount", type="integer", example=100),
 *             @OA\Property(property="diary_note_id", type="integer", example=33),
 *             @OA\Property(property="user_meal_id", type="integer", nullable=true)
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=404,
 *         description="Recipe not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Recipe not found")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=422,
 *         description="Missing required parameter",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="diary_note_id is required")
 *         )
 *     ),
 *     
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 * 
 * @OA\Post(
 *     path="/api/diary/meal/recepie/add",
 *     summary="Add recipe to meal",
 *     description="Adds a recipe to the specified meal",
 *     operationId="addRecepieToMeal",
 *     tags={"Diary - Recipes"},
 *     security={{"userSanctumToken": {}}},
 * 
 *     @OA\Parameter(
 *         name="diary_note_id",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="integer", example=33),
 *         description="Diary note ID from `GET /api/diary` response"
 *     ),
 *     
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"recepie_id", "meal_type", "amount"},
 *                 @OA\Property(property="recepie_id", type="integer", example=1, description="**REQUIRED**. Recipe ID"),
 *                 @OA\Property(property="meal_type", type="string", enum={"breakfast", "lunch", "dinner", "snack"}, description="**REQUIRED**. Type of meal"),
 *                 @OA\Property(property="amount", type="integer", example=100, description="**REQUIRED**. Amount in grams")
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Recipe added successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Recipe added to meal successfully"),
 *             @OA\Property(property="user_meal_id", type="integer", example=50),
 *             @OA\Property(property="diary_note_id", type="integer", example=33)
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=404,
 *         description="Diary note not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Diary note not found")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="recepie_id is required")
 *         )
 *     ),
 *     
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 * 
 * @OA\Put(
 *     path="/api/diary/meal/recepie/update",
 *     summary="Update recipe amount in meal",
 *     description="Updates the amount of a recipe in a meal",
 *     operationId="updateRecepieInMeal",
 *     tags={"Diary - Recipes"},
 *     security={{"userSanctumToken": {}}},
 * 
 *     @OA\Parameter(
 *         name="diary_note_id",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="integer", example=33),
 *         description="Diary note ID from `GET /api/diary` response"
 *     ),
 *     
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"user_meal_id", "amount"},
 *                 @OA\Property(property="user_meal_id", type="integer", example=45, description="**REQUIRED**. User meal ID"),
 *                 @OA\Property(property="amount", type="integer", example=150, description="**REQUIRED**. New amount in grams")
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Recipe amount updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Recipe amount updated successfully"),
 *             @OA\Property(property="user_meal_id", type="integer", example=49),
 *             @OA\Property(property="diary_note_id", type="integer", example=33)
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=404,
 *         description="Diary note not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Diary note not found")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="user_meal_id is required")
 *         )
 *     ),
 *     
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 * @OA\Post(
 *     path="/api/diary/meal/recepie/create",
 *     summary="Create a user recipe",
 *     description="Creates a user recipe and sends it to moderation. The author can use it immediately; other users see it only after approval.",
 *     operationId="createUserRecepie",
 *     tags={"Diary - Recipes"},
 *     security={{"userSanctumToken": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"name", "instructions", "calories", "proteins", "fats", "carbs"},
 *                 @OA\Property(property="name", type="string", example="Oatmeal"),
 *                 @OA\Property(property="instructions", type="string", example="Mix oats with water"),
 *                 @OA\Property(property="calories", type="number", example=150),
 *                 @OA\Property(property="proteins", type="number", example=5),
 *                 @OA\Property(property="fats", type="number", example=3),
 *                 @OA\Property(property="carbs", type="number", example=27),
 *                 @OA\Property(
 *                     property="ingredient_ids",
 *                     type="array",
 *                     @OA\Items(type="integer"),
 *                     example={1, 2}
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Recipe submitted for moderation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Recipe submitted for moderation"),
 *             @OA\Property(property="recepie", ref="#/components/schemas/UserRecepie")
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 */

class RecepieController extends Controller
{
    //
}
