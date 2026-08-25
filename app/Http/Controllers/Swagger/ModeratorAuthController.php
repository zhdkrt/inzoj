<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;

/**
 * @OA\Tag(
 *     name="Moderator Authentication",
 *     description="Moderator authentication endpoints"
 * )
 *
 * @OA\Post(
 *     path="/api/moderator/login",
 *     summary="Moderator login",
 *     operationId="moderatorLogin",
 *     tags={"Moderator Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"email", "password"},
 *                 @OA\Property(property="email", type="string", format="email", example="moderator@gmail.com"),
 *                 @OA\Property(property="password", type="string", format="password", example="password")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login successful",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="token", type="string", example="1|abcdefghijklmnopqrstuvwxyz"),
 *             @OA\Property(
 *                 property="user",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="email", type="string", example="moderator@gmail.com"),
 *                 @OA\Property(property="name", type="string", nullable=true, example="Moderator")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Invalid credentials"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Post(
 *     path="/api/moderator/logout",
 *     summary="Moderator logout",
 *     operationId="moderatorLogout",
 *     tags={"Moderator Authentication"},
 *     security={{"moderatorSanctumToken": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Logout successful",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Logged out successfully")
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Moderator access required")
 * )
 */
class ModeratorAuthController extends Controller
{
    //
}
