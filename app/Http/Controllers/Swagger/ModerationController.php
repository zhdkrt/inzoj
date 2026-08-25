<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;

/**
 * @OA\Tag(
 *     name="Moderation",
 *     description="Moderator queue for recipes, restaurants and trainers"
 * )
 *
 * @OA\Get(
 *     path="/api/moderator/queue",
 *     summary="Pending counts",
 *     operationId="moderatorQueue",
 *     tags={"Moderation"},
 *     security={{"moderatorSanctumToken": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Pending counts",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="pending",
 *                 type="object",
 *                 @OA\Property(property="recipes", type="integer", example=3),
 *                 @OA\Property(property="restaurants", type="integer", example=1),
 *                 @OA\Property(property="trainers", type="integer", example=2)
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Moderator access required")
 * )
 *
 * @OA\Get(
 *     path="/api/moderator/recipes",
 *     summary="List user recipes for moderation",
 *     operationId="moderatorRecipes",
 *     tags={"Moderation"},
 *     security={{"moderatorSanctumToken": {}}},
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         @OA\Schema(type="string", enum={"pending", "approved", "rejected", "all"}, default="pending")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Recipe list",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="recipes", type="array", @OA\Items(ref="#/components/schemas/UserRecepie"))
 *         )
 *     )
 * )
 *
 * @OA\Get(
 *     path="/api/moderator/recipes/{id}",
 *     summary="Get user recipe details",
 *     operationId="moderatorShowRecipe",
 *     tags={"Moderation"},
 *     security={{"moderatorSanctumToken": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Recipe details"),
 *     @OA\Response(response=404, description="Not found")
 * )
 *
 * @OA\Post(
 *     path="/api/moderator/recipes/{id}",
 *     summary="Approve or reject a user recipe",
 *     operationId="moderatorReviewRecipe",
 *     tags={"Moderation"},
 *     security={{"moderatorSanctumToken": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"action"},
 *                 @OA\Property(property="action", type="string", enum={"approve", "reject"}, example="approve"),
 *                 @OA\Property(property="comment", type="string", nullable=true, example="Looks good")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="Reviewed"),
 *     @OA\Response(response=404, description="Not found"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Get(
 *     path="/api/moderator/restaurants",
 *     summary="List restaurants for moderation",
 *     operationId="moderatorRestaurants",
 *     tags={"Moderation"},
 *     security={{"moderatorSanctumToken": {}}},
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         @OA\Schema(type="string", enum={"pending", "approved", "rejected", "all"}, default="pending")
 *     ),
 *     @OA\Response(response=200, description="Restaurant list")
 * )
 *
 * @OA\Get(
 *     path="/api/moderator/restaurants/{id}",
 *     summary="Get restaurant details",
 *     operationId="moderatorShowRestaurant",
 *     tags={"Moderation"},
 *     security={{"moderatorSanctumToken": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Restaurant details"),
 *     @OA\Response(response=404, description="Not found")
 * )
 *
 * @OA\Post(
 *     path="/api/moderator/restaurants/{id}",
 *     summary="Approve or reject a restaurant",
 *     operationId="moderatorReviewRestaurant",
 *     tags={"Moderation"},
 *     security={{"moderatorSanctumToken": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"action"},
 *                 @OA\Property(property="action", type="string", enum={"approve", "reject"}, example="approve"),
 *                 @OA\Property(property="comment", type="string", nullable=true)
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="Reviewed"),
 *     @OA\Response(response=404, description="Not found")
 * )
 *
 * @OA\Get(
 *     path="/api/moderator/trainers",
 *     summary="List trainers for moderation",
 *     operationId="moderatorTrainers",
 *     tags={"Moderation"},
 *     security={{"moderatorSanctumToken": {}}},
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         @OA\Schema(type="string", enum={"pending", "approved", "rejected", "all"}, default="pending")
 *     ),
 *     @OA\Response(response=200, description="Trainer list")
 * )
 *
 * @OA\Get(
 *     path="/api/moderator/trainers/{id}",
 *     summary="Get trainer details",
 *     operationId="moderatorShowTrainer",
 *     tags={"Moderation"},
 *     security={{"moderatorSanctumToken": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Trainer details"),
 *     @OA\Response(response=404, description="Not found")
 * )
 *
 * @OA\Post(
 *     path="/api/moderator/trainers/{id}",
 *     summary="Approve or reject a trainer",
 *     operationId="moderatorReviewTrainer",
 *     tags={"Moderation"},
 *     security={{"moderatorSanctumToken": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"action"},
 *                 @OA\Property(property="action", type="string", enum={"approve", "reject"}, example="approve"),
 *                 @OA\Property(property="comment", type="string", nullable=true)
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="Reviewed"),
 *     @OA\Response(response=404, description="Not found")
 * )
 */
class ModerationController extends Controller
{
    //
}
