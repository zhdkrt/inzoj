<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Restaurant Authentication",
 *     description="Restaurant authentication endpoints (Restaurant User)"
 * )
 * 
 * @OA\Post(
 *     path="/api/restaurant/register",
 *     summary="Restaurant registration",
 *     description="Register a restaurant. The restaurant stays hidden until a moderator approves it.",
 *     operationId="restaurantRegister",
 *     tags={"Restaurant Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"email", "password", "password_confirmation", "restaurant_name", "restaurant_type"},
 *                 @OA\Property(property="email", type="string", format="email", example="new.restaurant@gmail.com"),
 *                 @OA\Property(property="password", type="string", format="password", example="password123"),
 *                 @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
 *                 @OA\Property(property="restaurant_name", type="string", example="New Cafe"),
 *                 @OA\Property(property="restaurant_type", type="string", enum={"cafe", "canteen", "fine_restaurant", "fast_food", "bistro", "pub", "bar", "other"}),
 *                 @OA\Property(property="name", type="string", nullable=true, example="Contact name")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Registration successful, pending moderation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="token", type="string"),
 *             @OA\Property(property="moderation_status", type="string", example="pending"),
 *             @OA\Property(property="message", type="string")
 *         )
 *     ),
 *     @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Post(
 *     path="/api/restaurant/login",
 *     summary="Restaurant login",
 *     description="Authenticate restaurant and return access token",
 *     operationId="restaurantLogin",
 *     tags={"Restaurant Authentication"},
 *     
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"email", "password"},
 *                 @OA\Property(property="email", type="string", format="email", example="maslow.r@gmail.com"),
 *                 @OA\Property(property="password", type="string", format="password", example="password")
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Login successful",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="token", type="string", example="1|abcdefghijklmnopqrstuvwxyz"),
 *             @OA\Property(
 *                 property="user",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="email", type="string", example="maslow.r@gmail.com"),
 *                 @OA\Property(property="name", type="string", nullable=true, example="Best Restaurant")
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=401,
 *         description="Invalid credentials",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Неверный email или пароль.")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 @OA\Property(
 *                     property="email",
 *                     type="array",
 *                     @OA\Items(type="string", example="The email field is required.")
 *                 ),
 *                 @OA\Property(
 *                     property="password",
 *                     type="array",
 *                     @OA\Items(type="string", example="The password field is required.")
 *                 )
 *             )
 *         )
 *     )
 * )
 * 
 * @OA\Post(
 *     path="/api/restaurant/logout",
 *     summary="Restaurant logout",
 *     description="Logout restaurant and invalidate token",
 *     operationId="restaurantLogout",
 *     tags={"Restaurant Authentication"},
 *     security={{"restaurantSanctumToken": {}}},
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Logout successful",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Logged out successfully")
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
 */

class RestaurantAuthController extends Controller
{
    //
}
