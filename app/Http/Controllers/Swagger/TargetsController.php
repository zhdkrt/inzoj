<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
     * @OA\Tag(
     *     name="Targets",
     *     description="User targets"
     * )
     * 
     * @OA\Get(
     *     path="/api/profile/targets",
     *     summary="Get user targets",
     *     description="Retrieve user targets and goals information",
     *     operationId="getTargets",
     *     tags={"Targets"},
     *     security={{"userSanctumToken": {}}},
     *     
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="User ID (optional, returns current user if not provided)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 ref="#/components/schemas/User"
     *             ),
     *             @OA\Property(
     *                 property="fields",
     *                 type="object",
     *                 description="Available fields with their display names",
     *                 @OA\AdditionalProperties(
     *                     type="string",
     *                     example="Цель"
     *                 )
     *             ),
     *             @OA\Property(property="editing", type="boolean", example=false),
 *             @OA\Property(property="plan", type="object", description="Calculated daily/weekly limits and BMI")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     * 
     * @OA\Get(
     *     path="/api/profile/targets/edit",
     *     summary="Get edit form for specific target field",
     *     description="Retrieve user target field data for editing",
     *     operationId="editTargetField",
     *     tags={"Targets"},
     *     security={{"userSanctumToken": {}}},
     *     
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="User ID (optional, uses current user if not provided)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     
     *     @OA\Parameter(
     *         name="field",
     *         in="query",
     *         description="Target field to edit",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"goal", "current_weight", "target_weight", "activity_level", "calories", "proteins", "fats", "carbs", "water", "steps"},
     *             example="goal"
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Edit form data retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 ref="#/components/schemas/User"
     *             ),
     *             @OA\Property(
     *                 property="fields",
     *                 type="object",
     *                 @OA\AdditionalProperties(type="string")
     *             ),
     *             @OA\Property(property="editing", type="boolean", example=true),
     *             @OA\Property(property="editingField", type="string", example="water"),
     *             @OA\Property(property="current_value", type="mixed", description="Current value of the field being edited")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=400,
     *         description="Non-existent field",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Non-existent field")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     * 
     * @OA\Patch(
     *     path="/api/profile/targets/update",
     *     summary="Update user target field",
     *     description="Update a specific target field for a user",
     *     operationId="updateTargetField",
     *     tags={"Targets"},
     *     security={{"userSanctumToken": {}}},
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"field", "value"},
     *                 @OA\Property(
     *                     property="user_id",
     *                     type="integer",
     *                     description="User ID (optional, uses current user if not provided)",
     *                     example=1,
     *                     nullable=true
     *                 ),
     *                 @OA\Property(
     *                     property="field",
     *                     type="string",
     *                     description="Field to update",
     *                     enum={"goal", "target_weight", "current_weight", "activity_level", "calories", "proteins", "fats", "carbs", "water", "steps"},
     *                     example="water"
     *                 ),
     *                 @OA\Property(
     *                     property="value",
     *                     type="mixed",
     *                     description="New value for the field (type depends on field)",
     *                     oneOf={
     *                         @OA\Schema(
     *                             type="string",
     *                             description="For goal field",
     *                             enum={"lose_weight", "gain_muscle", "maintain", "other"}
     *                         ),
     *                         @OA\Schema(
     *                             type="number",
     *                             format="float",
     *                             description="For weight fields (kg)",
     *                             minimum=30,
     *                             maximum=300,
     *                             example=75.5
     *                         ),
     *                         @OA\Schema(
     *                             type="string",
     *                             description="For activity level",
     *                             enum={"low", "medium", "high", "expert"},
     *                             example="medium"
     *                         ),
 *                         @OA\Schema(
 *                             type="integer",
 *                             description="For daily calorie goal",
 *                             minimum=1000,
 *                             maximum=5000,
 *                             example=2000
 *                         ),
 *                         @OA\Schema(
 *                             type="number",
 *                             format="float",
 *                             description="For proteins/fats/carbs (grams)",
 *                             minimum=0,
 *                             maximum=500,
 *                             example=135
 *                         ),
     *                         @OA\Schema(
     *                             type="number",
     *                             format="float",
     *                             description="For daily water intake (liters)",
     *                             minimum=1.0,
     *                             maximum=3.0,
     *                             example=2.0
     *                         ),
     *                         @OA\Schema(
     *                             type="integer",
     *                             description="For daily step goal",
     *                             minimum=1000,
     *                             maximum=40000,
     *                             example=10000
     *                         )
     *                     }
     *                 )
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Field updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Данные обновлены"),
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or non-existent field",
     *         @OA\JsonContent(
     *             type="object",
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="success", type="boolean", example=false),
     *                     @OA\Property(property="message", type="string", example="Non-existent field")
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="message", type="string", example="The value field is required."),
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         @OA\AdditionalProperties(
     *                             type="array",
     *                             @OA\Items(type="string")
     *                         )
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="value",
     *                     type="array",
     *                     @OA\Items(type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

class TargetsController extends Controller
{
    //
}
