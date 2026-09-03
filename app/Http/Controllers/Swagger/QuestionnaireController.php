<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Questionnaire",
 *     description="User onboarding questionnaire"
 * )
 * 
 * @OA\Get(
 *     path="/api/questionnaire",
 *     summary="Get current questionnaire step",
 *     description="Returns current step and user data",
 *     operationId="getQuestionnaire",
 *     tags={"Questionnaire"},
 *     security={{"userSanctumToken": {}}},
 *     
 *     @OA\Parameter(
 *         name="step",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string", enum={"name", "goal", "weight", "height", "age", "gender", "activity"}),
 *         description="Specific step to display"
 *     ),
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Questionnaire data retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="step", type="string", enum={"name", "goal", "weight", "height", "age", "gender", "activity"}, example="name"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="name", type="string", nullable=true),
 *                 @OA\Property(property="goal", type="string", enum={"lose_weight", "gain_muscle", "maintain", "other"}, nullable=true),
 *                 @OA\Property(property="current_weight", type="number", format="float", nullable=true),
 *                 @OA\Property(property="target_weight", type="number", format="float", nullable=true),
 *                 @OA\Property(property="height", type="integer", nullable=true),
 *                 @OA\Property(property="age", type="integer", nullable=true),
 *                 @OA\Property(property="gender", type="string", enum={"male", "female"}, nullable=true),
 *                 @OA\Property(property="activity_level", type="string", enum={"low", "medium", "high", "expert"}, nullable=true)
 *             ),
 *             @OA\Property(
 *                 property="steps",
 *                 type="array",
 *                 @OA\Items(type="string"),
 *                 example={"name", "goal", "weight", "height", "age", "gender", "activity"}
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=404, description="User not found")
 * )
 * 
 * @OA\Put(
 *     path="/api/questionnaire/update",
 *     summary="Update questionnaire step",
     *     description="Saves data for current step and returns next step. Completing activity writes Mifflin/extended nutrition targets (calories, KBJU, water, steps).",
 *     operationId="updateQuestionnaire",
 *     tags={"Questionnaire"},
 *     security={{"userSanctumToken": {}}},
 *     
 *     @OA\Parameter(
 *         name="step",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string", enum={"name", "goal", "weight", "height", "age", "gender", "activity"}),
 *         description="Current step (default: name)"
 *     ),
 *     
 *     @OA\Parameter(
 *         name="action",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string", enum={"next", "prev"}, default="next"),
 *         description="Navigation direction"
 *     ),
 *     
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 oneOf={
 *                     @OA\Schema(
 *                         required={"name"},
 *                         @OA\Property(property="name", type="string", maxLength=255, example="John Doe")
 *                     ),
 *                     @OA\Schema(
 *                         required={"goal"},
 *                         @OA\Property(property="goal", type="string", enum={"lose_weight", "gain_muscle", "maintain", "other"}, example="lose_weight")
 *                     ),
 *                     @OA\Schema(
 *                         required={"current_weight"},
 *                         @OA\Property(property="current_weight", type="number", format="float", example=75.5),
 *                         @OA\Property(property="target_weight", type="number", format="float", example=70.0, nullable=true)
 *                     ),
 *                     @OA\Schema(
 *                         required={"height"},
 *                         @OA\Property(property="height", type="integer", example=175)
 *                     ),
 *                     @OA\Schema(
 *                         required={"age"},
 *                         @OA\Property(property="age", type="integer", example=30)
 *                     ),
 *                     @OA\Schema(
 *                         required={"gender"},
 *                         @OA\Property(property="gender", type="string", enum={"male", "female"}, example="female")
 *                     ),
 *                     @OA\Schema(
 *                         required={"activity_level"},
 *                         @OA\Property(property="activity_level", type="string", enum={"low", "medium", "high", "expert"}, example="medium")
 *                     )
 *                 }
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Step completed successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Step completed successfully"),
 *             @OA\Property(property="next_step", type="string", nullable=true),
 *             @OA\Property(property="is_complete", type="boolean", example=false),
 *             @OA\Property(property="redirect_to", type="string", nullable=true),
 *             @OA\Property(property="user", ref="#/components/schemas/User"),
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 @OA\Property(
 *                     property="name",
 *                     type="array",
 *                     @OA\Items(type="string", example="The name field is required.")
 *                 )
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=404, description="User not found")
 * )
 */

class QuestionnaireController extends Controller
{
    //
}
