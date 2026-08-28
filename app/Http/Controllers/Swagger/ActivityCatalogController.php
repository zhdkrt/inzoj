<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;

/**
 * @OA\Tag(
 *     name="Moderator - Activities",
 *     description="Import and edit the global exercise catalog, including video URLs for the paid Activity tab"
 * )
 *
 * @OA\Get(
 *     path="/api/moderator/activities",
 *     summary="List global catalog",
 *     operationId="moderatorActivities",
 *     tags={"Moderator - Activities"},
 *     security={{"moderatorSanctumToken": {}}},
 *     @OA\Parameter(name="q", in="query", @OA\Schema(type="string")),
 *     @OA\Parameter(name="location_type", in="query", @OA\Schema(type="string", enum={"home", "gym", "outdoor", "any"})),
 *     @OA\Parameter(name="category", in="query", @OA\Schema(type="string")),
 *     @OA\Response(response=200, description="Catalog list")
 * )
 *
 * @OA\Post(
 *     path="/api/moderator/activities",
 *     summary="Create or update one catalog exercise by name",
 *     operationId="moderatorStoreActivity",
 *     tags={"Moderator - Activities"},
 *     security={{"moderatorSanctumToken": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"name"},
 *                 @OA\Property(property="name", type="string", example="Отжимания"),
 *                 @OA\Property(property="time", type="string", example="10 мин"),
 *                 @OA\Property(property="calories", type="integer", example=80),
 *                 @OA\Property(property="location_type", type="string", enum={"home", "gym", "outdoor", "any"}, example="home"),
 *                 @OA\Property(property="category", type="string", example="chest"),
 *                 @OA\Property(property="video_url", type="string", nullable=true, example="https://example.com/pushups.mp4"),
 *                 @OA\Property(property="is_premium", type="boolean", example=true)
 *             )
 *         )
 *     ),
 *     @OA\Response(response=201, description="Created"),
 *     @OA\Response(response=200, description="Updated existing name")
 * )
 *
 * @OA\Post(
 *     path="/api/moderator/activities/import",
 *     summary="Bulk import catalog",
 *     description="Upsert global exercises by name. Use this when the video course database is delivered.",
 *     operationId="moderatorImportActivities",
 *     tags={"Moderator - Activities"},
 *     security={{"moderatorSanctumToken": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"activities"},
 *                 @OA\Property(
 *                     property="activities",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         required={"name"},
 *                         @OA\Property(property="name", type="string"),
 *                         @OA\Property(property="time", type="string"),
 *                         @OA\Property(property="calories", type="integer"),
 *                         @OA\Property(property="location_type", type="string", enum={"home", "gym", "outdoor", "any"}),
 *                         @OA\Property(property="category", type="string"),
 *                         @OA\Property(property="video_url", type="string", nullable=true),
 *                         @OA\Property(property="is_premium", type="boolean")
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="created/updated counts")
 * )
 *
 * @OA\Put(
 *     path="/api/moderator/activities/{id}",
 *     summary="Update a catalog exercise",
 *     operationId="moderatorUpdateActivity",
 *     tags={"Moderator - Activities"},
 *     security={{"moderatorSanctumToken": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="time", type="string"),
 *                 @OA\Property(property="calories", type="integer"),
 *                 @OA\Property(property="location_type", type="string", enum={"home", "gym", "outdoor", "any"}),
 *                 @OA\Property(property="category", type="string"),
 *                 @OA\Property(property="video_url", type="string", nullable=true),
 *                 @OA\Property(property="is_premium", type="boolean")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="Updated"),
 *     @OA\Response(response=404, description="Not found")
 * )
 */
class ActivityCatalogController extends Controller
{
    //
}
