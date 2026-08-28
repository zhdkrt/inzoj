<?php

namespace App\Http\Controllers\Diary;

use App\Models\Activity;
use App\Models\UserActivity;
use App\Models\DiaryNote;
use App\Models\UserFavoriteActivity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainingController extends Controller
{
    public function show(Request $request)
    {
        $userActivityId = $request->get('user_activity_id');
        [$userId, $diaryNoteId, $trainingId, $timeType, $timeCount, $calories] = $this->getData($request, $userActivityId);

        $user = Auth::user();
        $activity = Activity::visibleTo($user)->find($trainingId);
        if (!$activity && $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Training not found',
            ], 404);
        }

        $favoriteIds = UserFavoriteActivity::where('user_id', $userId)->pluck('activity_id')->all();
        $adding = $userActivityId !== null ? false : true;
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'activity' => $activity ? $activity->toApiArray($user, in_array((int) $activity->id, $favoriteIds, true)) : null,
                'user_activity_id' => $userActivityId,
                'user_time_type' => $timeType,
                'user_time_count' => $timeCount,
                'user_calories' => $calories,
                'adding' => $adding,
                'diary_note_id' => $diaryNoteId,
            ]);
        }

        return view('diary.training', [
            'training' => $activity,
            'userActivityId' => $userActivityId,
            'timeType' => $timeType,
            'timeCount' => $timeCount,
            'calories' => $calories,
            'adding' => $adding,
        ]);
    }

    public function createActivity(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'time' => 'nullable|string|max:50',
            'calories' => 'nullable|integer|min:0|max:2000',
            'location_type' => 'nullable|in:'.implode(',', Activity::LOCATIONS),
            'category' => 'nullable|string|max:50',
        ]);

        $activity = Activity::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'time' => $validated['time'] ?? '10 мин',
            'calories' => $validated['calories'] ?? 50,
            'location_type' => $validated['location_type'] ?? Activity::LOCATION_ANY,
            'category' => $validated['category'] ?? null,
            'video_url' => null,
            'is_premium' => false,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Custom activity created',
                'activity' => $activity->toApiArray($user),
            ], 201);
        }

        return redirect()->route('activity');
    }

    public function addTraining(Request $request)
    {
        [$userId, $diaryNoteId, $trainingId, $timeType, $timeCount, $calories] = $this->getData($request);

        $activity = Activity::visibleTo(Auth::user())->find($trainingId);
        if (!$activity) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Training not found',
                ], 404);
            }
            abort(404, 'Training not found');
        }

        $userActivity = UserActivity::create([
            'user_id' => $userId,
            'diary_note_id' => $diaryNoteId,
            'activity_id' => $trainingId,
            'time_count' => $timeCount,
            'time_type' => $timeType,
            'calories' => $calories,
        ]);

        return $this->recount($diaryNoteId, true, $request, $userActivity->id);
    }

    public function updateTraining(Request $request)
    {
        [$userId, $diaryNoteId, $trainingId, $timeType, $timeCount, $calories] = $this->getData($request);

        $userActivityId = $request->get('user_activity_id');
        $userActivity = UserActivity::where('user_id', $userId)->find($userActivityId);

        if (!$userActivity) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User activity not found',
                ], 404);
            }
            abort(404, 'User activity not found');
        }

        $userActivity->update([
            'time_count' => $timeCount,
            'time_type' => $timeType,
            'calories' => $calories,
        ]);

        return $this->recount($userActivity->diary_note_id, false, $request, $userActivity->id);
    }

    public function deleteTraining(Request $request, $id)
    {
        $userActivity = UserActivity::where('user_id', Auth::id())->find($id);

        if (!$userActivity) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User activity not found',
                ], 404);
            }
            abort(404, 'User activity not found');
        }

        $diaryNoteId = $userActivity->diary_note_id;
        $userActivity->delete();

        return $this->recount($diaryNoteId, false, $request, null, true);
    }

    private function recount($diaryNoteId, $adding, $request, $userActivityId, $deleted = false)
    {
        $diaryNote = DiaryNote::find($diaryNoteId);

        if (!$diaryNote) {
            if ($request && $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Diary note not found',
                ], 404);
            }
            return redirect()->route('diary')->with('error', 'Diary note not found');
        }

        $sumCalories = $diaryNote->recountBurnedCalories();

        if ($request && $request->expectsJson()) {
            $message = 'Training updated successfully';
            if ($deleted) {
                $message = 'Training deleted successfully';
            } elseif ($adding) {
                $message = 'Training added successfully';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'user_activity_id' => $userActivityId,
                'burned_calories' => $sumCalories,
                'diary_note_id' => $diaryNoteId,
            ]);
        }

        return redirect()->route('diary');
    }

    private function getData(Request $request, $userActivityId = null)
    {
        $userId = Auth::user()->id;
        $diaryNoteId = $request->get('diary_note_id') ?? session('diary_note_id');
        $userActivity = $userActivityId ? UserActivity::where('user_id', $userId)->find($userActivityId) : null;

        if ($userActivity) {
            $trainingId = $userActivity->activity_id;
            $timeCount = $userActivity->time_count;
            $timeType = $userActivity->time_type;
            $calories = $userActivity->calories;
        } else {
            $trainingId = $request->get('training_id');
            $timeCount = $request->get('time_count');
            $timeType = $request->get('time_type');
            $calories = $request->get('calories');
        }

        return [$userId, $diaryNoteId, $trainingId, $timeType, $timeCount, $calories, $userActivityId];
    }
}
