<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Diary - Trainings",
 *     description="Training management in diary"
 * )
 * 
 * @OA\Get(
 *     path="/api/diary/activity/training",
 *     summary="Get training details",
 *     description="Returns training information for editing or adding to diary",
 *     operationId="getTraining",
 *     tags={"Diary - Trainings"},
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
 *         name="training_id",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="integer", example=2),
 *         description="Training activity ID"
 *     ),
 *     
 *     @OA\Parameter(
 *         name="user_activity_id",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="integer", example=10),
 *         description="User activity ID (for editing existing training)"
 *     ),
 *          
 *     @OA\Response(
 *         response=200,
 *         description="Training retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="activity", ref="#/components/schemas/Activity"),
 *             @OA\Property(property="user_activity_id", type="integer", nullable=true),
 *             @OA\Property(property="user_time_type", type="string", nullable=true),
 *             @OA\Property(property="user_time_count", type="integer", nullable=true),
 *             @OA\Property(property="user_calories", type="integer", nullable=true),
 *             @OA\Property(property="adding", type="boolean", example=true),
 *             @OA\Property(property="diary_note_id", type="integer", example=33)
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=404,
 *         description="Training not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Training not found")
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
 *     path="/api/diary/activity/training/add",
 *     summary="Add training to diary",
 *     description="Adds a training activity to the diary",
 *     operationId="addTraining",
 *     tags={"Diary - Trainings"},
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
 *                 required={"training_id", "time_count", "time_type", "calories"},
 *                 @OA\Property(property="training_id", type="integer", example=2, description="**REQUIRED**. Training activity ID"),
 *                 @OA\Property(property="time_count", type="integer", example=30, description="**REQUIRED**. Duration value"),
 *                 @OA\Property(property="time_type", type="string", enum={"minute", "hour"}, example="minute", description="**REQUIRED**. Time unit type"),
 *                 @OA\Property(property="calories", type="integer", example=200, description="**REQUIRED**. Calories burned")
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Training added successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Training added successfully"),
 *             @OA\Property(property="user_activity_id", type="integer", example=10),
 *             @OA\Property(property="burned_calories", type="integer", example=200),
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
 *             @OA\Property(property="message", type="string", example="training_id is required")
 *         )
 *     ),
 *     
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 * 
 * @OA\Put(
 *     path="/api/diary/activity/training/update",
 *     summary="Update training in diary",
 *     description="Updates an existing training activity in the diary",
 *     operationId="updateTraining",
 *     tags={"Diary - Trainings"},
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
 *         name="user_activity_id",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="integer", example=10),
 *         description="**REQUIRED**. User activity ID"
 *     ),
 *     
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"time_count", "time_type", "calories"},
 *                 @OA\Property(property="time_count", type="integer", example=45, description="**REQUIRED**. Duration value"),
 *                 @OA\Property(property="time_type", type="string", enum={"minute", "hour"}, description="**REQUIRED**. Time unit type"),
 *                 @OA\Property(property="calories", type="integer", example=300, description="**REQUIRED**. Calories burned")
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Training updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Training updated successfully"),
 *             @OA\Property(property="user_activity_id", type="integer", example=10),
 *             @OA\Property(property="burned_calories", type="integer", example=300),
 *             @OA\Property(property="diary_note_id", type="integer", example=33)
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=404,
 *         description="User activity not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="User activity not found")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="user_activity_id is required")
 *         )
 *     ),
 *     
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 * @OA\Post(
 *     path="/api/diary/activity/training/create",
 *     summary="Create a custom exercise",
 *     description="Adds a personal catalog item visible only to the current user. Then add it to the diary via POST /api/diary/activity/training/add.",
 *     operationId="createCustomActivity",
 *     tags={"Diary - Trainings"},
 *     security={{"userSanctumToken": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"name"},
 *                 @OA\Property(property="name", type="string", example="Мои отжимания"),
 *                 @OA\Property(property="time", type="string", example="10 мин"),
 *                 @OA\Property(property="calories", type="integer", example=70),
 *                 @OA\Property(property="location_type", type="string", enum={"home", "gym", "outdoor", "any"}),
 *                 @OA\Property(property="category", type="string", example="chest")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=201, description="Created"),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 * @OA\Delete(
 *     path="/api/diary/activity/training/{id}",
 *     summary="Delete a diary workout",
 *     description="id is user_activity_id from GET /api/diary",
 *     operationId="deleteDiaryTraining",
 *     tags={"Diary - Trainings"},
 *     security={{"userSanctumToken": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Deleted"),
 *     @OA\Response(response=404, description="Not found"),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 */

class TrainingController extends Controller
{
    //
}
