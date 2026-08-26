<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;

/**
 * @OA\Tag(
 *     name="Stats",
 *     description="Progress statistics: weight and calories are free; measurements and photos require premium"
 * )
 *
 * @OA\Schema(
 *     schema="BodyLog",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="type", type="string", example="weight"),
 *     @OA\Property(property="value", type="number", example=75.5),
 *     @OA\Property(property="unit", type="string", example="kg"),
 *     @OA\Property(property="logged_at", type="string", format="date", example="2026-08-25"),
 *     @OA\Property(property="note", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ProgressPhoto",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="kind", type="string", enum={"before", "after", "progress"}),
 *     @OA\Property(property="taken_at", type="string", format="date"),
 *     @OA\Property(property="weight_at_time", type="number", nullable=true, example=75.5),
 *     @OA\Property(property="note", type="string", nullable=true),
 *     @OA\Property(property="url", type="string", example="/api/stats/photos/1/file")
 * )
 *
 * @OA\Get(
 *     path="/api/stats/summary",
 *     summary="Progress summary",
 *     operationId="statsSummary",
 *     tags={"Stats"},
 *     security={{"userSanctumToken": {}}},
 *     @OA\Response(response=200, description="Start vs current weight, today calories; photos if premium")
 * )
 *
 * @OA\Get(
 *     path="/api/stats/series",
 *     summary="Chart series",
 *     description="Free metrics: weight, calories, burned_calories, proteins, fats, carbs, water, steps. Premium: neck, chest, waist, hips, biceps, glucose, blood_pressure_sys, blood_pressure_dia.",
 *     operationId="statsSeries",
 *     tags={"Stats"},
 *     security={{"userSanctumToken": {}}},
 *     @OA\Parameter(name="metric", in="query", required=true, @OA\Schema(type="string", example="calories")),
 *     @OA\Parameter(name="from", in="query", @OA\Schema(type="string", format="date", example="2026-06-01")),
 *     @OA\Parameter(name="to", in="query", @OA\Schema(type="string", format="date", example="2026-08-25")),
 *     @OA\Parameter(name="group", in="query", @OA\Schema(type="string", enum={"day", "week"}, default="day")),
 *     @OA\Response(
 *         response=200,
 *         description="Points for charts",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="metric", type="string", example="calories"),
 *             @OA\Property(
 *                 property="points",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="date", type="string", example="2026-08-24"),
 *                     @OA\Property(property="value", type="number", example=1800)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(response=403, description="Premium required for this metric")
 * )
 *
 * @OA\Get(
 *     path="/api/stats/compare",
 *     summary="Before vs after",
 *     description="Weight is always returned. Photos are included only for premium users.",
 *     operationId="statsCompare",
 *     tags={"Stats"},
 *     security={{"userSanctumToken": {}}},
 *     @OA\Response(response=200, description="Start and current weight, optional photos")
 * )
 *
 * @OA\Get(
 *     path="/api/stats/logs",
 *     summary="List body logs",
 *     operationId="statsLogs",
 *     tags={"Stats"},
 *     security={{"userSanctumToken": {}}},
 *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string", example="weight")),
 *     @OA\Parameter(name="from", in="query", @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="to", in="query", @OA\Schema(type="string", format="date")),
 *     @OA\Response(response=200, description="Log list")
 * )
 *
 * @OA\Post(
 *     path="/api/stats/logs",
 *     summary="Create or update a log for a date",
 *     description="One value per type per day (upsert). Weight is free. Measurements, glucose and blood pressure require premium.",
 *     operationId="statsStoreLog",
 *     tags={"Stats"},
 *     security={{"userSanctumToken": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"type", "value"},
 *                 @OA\Property(property="type", type="string", example="weight", description="weight, neck, chest, waist, hips, biceps, glucose, blood_pressure_sys, blood_pressure_dia"),
 *                 @OA\Property(property="value", type="number", example=75.5),
 *                 @OA\Property(property="logged_at", type="string", format="date", example="2026-08-25"),
 *                 @OA\Property(property="note", type="string", nullable=true)
 *             )
 *         )
 *     ),
 *     @OA\Response(response=201, description="Saved"),
 *     @OA\Response(response=403, description="Premium required")
 * )
 *
 * @OA\Delete(
 *     path="/api/stats/logs/{id}",
 *     summary="Delete a log",
 *     operationId="statsDeleteLog",
 *     tags={"Stats"},
 *     security={{"userSanctumToken": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Deleted")
 * )
 *
 * @OA\Get(
 *     path="/api/stats/photos",
 *     summary="List progress photos",
 *     operationId="statsPhotos",
 *     tags={"Stats"},
 *     security={{"userSanctumToken": {}}},
 *     @OA\Response(response=200, description="Photos"),
 *     @OA\Response(response=403, description="Premium required")
 * )
 *
 * @OA\Post(
 *     path="/api/stats/photos",
 *     summary="Upload a progress photo",
 *     operationId="statsStorePhoto",
 *     tags={"Stats"},
 *     security={{"userSanctumToken": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"photo"},
 *                 @OA\Property(property="photo", type="string", format="binary"),
 *                 @OA\Property(property="kind", type="string", enum={"before", "after", "progress"}),
 *                 @OA\Property(property="taken_at", type="string", format="date"),
 *                 @OA\Property(property="weight", type="number", example=75.5),
 *                 @OA\Property(property="note", type="string")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=201, description="Saved"),
 *     @OA\Response(response=403, description="Premium required")
 * )
 *
 * @OA\Get(
 *     path="/api/stats/photos/{id}/file",
 *     summary="Download own progress photo",
 *     description="Private file. Requires user Bearer token and premium. Frontend should fetch with Authorization, not a public img src.",
 *     operationId="statsShowPhotoFile",
 *     tags={"Stats"},
 *     security={{"userSanctumToken": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Image file"),
 *     @OA\Response(response=403, description="Premium required"),
 *     @OA\Response(response=404, description="Not found")
 * )
 *
 * @OA\Delete(
 *     path="/api/stats/photos/{id}",
 *     summary="Delete a progress photo",
 *     operationId="statsDeletePhoto",
 *     tags={"Stats"},
 *     security={{"userSanctumToken": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Deleted")
 * )
 */
class StatsController extends Controller
{
    //
}
