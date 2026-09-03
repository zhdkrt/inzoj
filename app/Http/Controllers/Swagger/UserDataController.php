<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="User Data",
 *     description="User personal data management (weight, height, age, gender)"
 * )
 * 
 * @OA\Get(
 *     path="/api/profile/user-data",
 *     summary="Get user data",
 *     description="Returns user personal data including weight, height, age and gender",
 *     operationId="getUserData",
 *     tags={"User Data"},
 *     security={{"userSanctumToken": {}}},
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
 *                 @OA\AdditionalProperties(type="string", example="Текущий вес")
 *             ),
 *             @OA\Property(property="editing", type="boolean", example=false),
 *             @OA\Property(property="calculated_data", type="object", description="BMR, BMI, calories for legacy clients"),
 *             @OA\Property(property="plan", type="object", description="Full nutrition/BMI plan from GET /api/profile/nutrition")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated")
 *         )
 *     )
 * )
 * 
 * @OA\Get(
 *     path="/api/profile/user-data/edit",
 *     summary="Get edit form for specific field",
 *     description="Returns user data for editing a specific field",
 *     operationId="editUserDataField",
 *     tags={"User Data"},
 *     security={{"userSanctumToken": {}}},
 *     
 *     @OA\Parameter(
 *         name="field",
 *         in="query",
 *         description="Field to edit",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *             enum={"current_weight", "height", "age", "gender"},
 *             example="height"
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
 *             @OA\Property(property="editingField", type="string", example="height")
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
 *         description="Unauthenticated",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated")
 *         )
 *     )
 * )
 * 
 * @OA\Patch(
 *     path="/api/profile/user-data/update",
 *     summary="Update user data field",
 *     description="Updates a specific user data field",
 *     operationId="updateUserDataField",
 *     tags={"User Data"},
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
 *                     property="field",
 *                     type="string",
 *                     description="Field to update",
 *                     enum={"current_weight", "height", "age", "gender"},
 *                     example="height"
 *                 ),
 *                 @OA\Property(
 *                     property="value",
 *                     type="mixed",
 *                     description="New value for the field",
 *                     oneOf={
 *                         @OA\Schema(
 *                             type="number",
 *                             format="float",
 *                             description="For current_weight (kg)",
 *                             minimum=30,
 *                             maximum=300,
 *                             example=175
 *                         ),
 *                         @OA\Schema(
 *                             type="integer",
 *                             description="For height (cm)",
 *                             minimum=100,
 *                             maximum=220,
 *                             example=175
 *                         ),
 *                         @OA\Schema(
 *                             type="integer",
 *                             description="For age",
 *                             minimum=18,
 *                             maximum=100,
 *                             example=30
 *                         ),
 *                         @OA\Schema(
 *                             type="string",
 *                             description="For gender",
 *                             enum={"male", "female"},
 *                             example="male"
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
 *             type="object",
 *             @OA\Property(property="message", type="string", example="The value field is required."),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 @OA\Property(
 *                     property="value",
 *                     type="array",
 *                     @OA\Items(type="string", example="The value must be an integer.")
 *                 )
 *             )
 *         )
 *     )
 * )
 */

class UserDataController extends Controller
{
    //
}
