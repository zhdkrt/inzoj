<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Ration",
 *     description="User diet preferences and allergies management"
 * )
 * 
 * @OA\Get(
 *     path="/api/profile/ration",
 *     summary="Get user ration preferences",
 *     description="Returns user's food preferences and allergies",
 *     operationId="getRation",
 *     tags={"Ration"},
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
 *                 property="all_allergies",
 *                 type="object",
 *                 description="All available allergies with their display names",
 *                 @OA\AdditionalProperties(type="string", example="Непереносимость лактозы")
 *             ),
 *             @OA\Property(
 *                 property="food_preferences",
 *                 type="string",
 *                 enum={"no_preferences", "vegan", "vegetarian"},
 *                 example="vegetarian"
 *             ),
 *             @OA\Property(
 *                 property="userAllergies",
 *                 type="array",
 *                 description="List of user's allergies",
 *                 @OA\Items(type="string", example="lactose")
 *             ),
 *             @OA\Property(
 *                 property="product_recommendations",
 *                 type="array",
 *                 description="Foods filtered by diet and allergies",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="name", type="string"),
 *                     @OA\Property(property="group", type="string"),
 *                     @OA\Property(property="role", type="string")
 *                 )
 *             )
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
 *     path="/api/profile/ration/update",
 *     summary="Update user ration preferences",
 *     description="Updates user's food preferences and allergies",
 *     operationId="updateRation",
 *     tags={"Ration"},
 *     security={{"userSanctumToken": {}}},
 *     
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"food_preferences"},
 *                 @OA\Property(
 *                     property="allergies",
 *                     type="array",
 *                     description="Array of allergy keys",
 *                     @OA\Items(
 *                         type="string",
 *                         enum={"lactose", "gluten", "wheat", "nuts", "crayfish", "eggs", "fish", "milk", "berries"},
 *                         example="lactose"
 *                     )
 *                 ),
 *                 @OA\Property(
 *                     property="food_preferences",
 *                     type="string",
 *                     description="Food preference type",
 *                     enum={"no_preferences", "vegan", "vegetarian"},
 *                     example="vegetarian"
 *                 )
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Preferences updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Данные обновлены"),
 *             @OA\Property(property="product_recommendations", type="array", @OA\Items(type="object"))
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
 *             @OA\Property(property="message", type="string", example="The food preferences field is required."),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 @OA\Property(
 *                     property="food_preferences",
 *                     type="array",
 *                     @OA\Items(type="string", example="The selected food preferences is invalid.")
 *                 ),
 *                 @OA\Property(
 *                     property="allergies.0",
 *                     type="array",
 *                     @OA\Items(type="string", example="The selected allergies.0 is invalid.")
 *                 )
 *             )
 *         )
 *     )
 * )
 */

class RationController extends Controller
{
    //
}
