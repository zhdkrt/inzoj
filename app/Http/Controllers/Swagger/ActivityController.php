<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Get(
 *     path="/api/diary/activity",
 *     summary="Get activity data",
 *     description="Returns walking steps or training activities",
 *     operationId="getActivity",
 *     tags={"Diary - Activity"},
 *     security={{"userSanctumToken": {}}},
 *        
 *     @OA\Parameter(
 *         name="diary_note_id",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="integer", example=33),
 *         description="Diary note ID from GET /api/diary"
 *     ),
 *   
 *     @OA\Parameter(
 *         name="activityType",
 *         in="query",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *             enum={"walking", "training"},
 *             default="training"
 *         ),
 *         description="Type of activity"
 *     ),
 *     @OA\Parameter(
 *         name="q",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string", example="Отжимания"),
 *         description="Search by exercise name (training only)"
 *     ),
 *     @OA\Parameter(
 *         name="location_type",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string", enum={"home", "gym", "outdoor", "any"}),
 *         description="Filter by place. home/gym/outdoor also include activities with location any"
 *     ),
 *     @OA\Parameter(
 *         name="category",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string", example="abs")
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="integer", example=20)
 *     ),
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Activity data retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="activity_type", type="string", enum={"walking", "training"}),
 *             @OA\Property(property="steps", type="integer", nullable=true),
 *             @OA\Property(
 *                 property="activities",
 *                 type="array",
 *                 nullable=true,
 *                 @OA\Items(ref="#/components/schemas/Activity")
 *             ),
 *             @OA\Property(
 *                 property="meta",
 *                 type="object",
 *                 nullable=true,
 *                 @OA\Property(property="current_page", type="integer", example=1),
 *                 @OA\Property(property="last_page", type="integer", example=2),
 *                 @OA\Property(property="per_page", type="integer", example=20),
 *                 @OA\Property(property="total", type="integer", example=21)
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(response=401, description="Unauthenticated"),
 * )
 * 
 * @OA\Post(
 *     path="/api/diary/activity/toggle-favorite",
 *     summary="Toggle activity favorite",
 *     description="Add or remove training activity from user's favorites",
 *     operationId="toggleActivityFavorite",
 *     tags={"Diary - Activity"},
 *     security={{"userSanctumToken": {}}},
 *     
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"training_id", "is_favorite"},
 *                 @OA\Property(
 *                     property="training_id",
 *                     type="integer",
 *                     example=1,
 *                     description="Training activity ID"
 *                 ),
 *                 @OA\Property(
 *                     property="is_favorite",
 *                     type="boolean",
 *                     example=false,
 *                     description="Current favorite status (false = add, true = remove)"
 *                 )
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Favorite toggled successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Activity added to favorites"
 *             ),
 *             @OA\Property(
 *                 property="is_favorite",
 *                 type="boolean",
 *                 example=true,
 *                 description="New favorite status"
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="training_id is required")
 *         )
 *     ),
 *     
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 * 
 * @OA\Put(
 *     path="/api/diary/activity/steps",
 *     summary="Update walking steps",
 *     description="Updates user's daily steps and calculates burned calories",
 *     operationId="updateSteps",
 *     tags={"Diary - Activity"},
 *     security={{"userSanctumToken": {}}},
 *     
 *     @OA\Parameter(
 *         name="diary_note_id",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="integer", example=33),
 *         description="Diary note ID from GET /api/diary"
 *     ),
 *     
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"stepsCount"},
 *                 @OA\Property(property="stepsCount", type="integer", example=8000, description="Number of steps")
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Steps updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Steps updated successfully"),
 *         )
 *     ),
 *     
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=404, description="Diary note not found"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 * 
 */

class ActivityController extends Controller
{
    //
}
