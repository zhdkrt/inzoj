<?php

namespace App\Http\Controllers\Diary;

use App\Models\Activity;
use App\Models\DiaryNote;
use App\Models\UserFavoriteActivity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();
        $diaryNoteId = $request->expectsJson() ? $request->get('diary_note_id') : session('diary_note_id');

        if (!$diaryNoteId && $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'diary_note_id is required',
            ], 422);
        }

        $activityType = $request->get('activityType') ?? 'training';

        if ($activityType == 'walking') {
            $note = DiaryNote::find($diaryNoteId);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'activity_type' => 'walking',
                    'steps' => $note ? $note->current_steps : 0,
                    'diary_note_id' => $diaryNoteId,
                ]);
            }
            return view('diary.steps', ['steps' => $note->current_steps]);
        }

        if ($activityType == 'training') {
            $validated = $request->validate([
                'q' => 'nullable|string|max:100',
                'location_type' => 'nullable|in:'.implode(',', Activity::LOCATIONS),
                'category' => 'nullable|string|max:50',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            $favoriteIds = UserFavoriteActivity::where('user_id', $user->id)
                ->pluck('activity_id')
                ->all();

            $query = Activity::visibleTo($user)
                ->forLocation($validated['location_type'] ?? null);

            if (!empty($validated['q'])) {
                $query->where('name', 'like', '%'.$validated['q'].'%');
            }
            if (!empty($validated['category'])) {
                $query->where('category', $validated['category']);
            }

            if (!empty($favoriteIds)) {
                $ids = implode(',', array_map('intval', $favoriteIds));
                $query->orderByRaw("CASE WHEN id IN ({$ids}) THEN 0 ELSE 1 END");
            }
            $query->orderBy('name');

            $perPage = $validated['per_page'] ?? 20;
            $paginator = $query->paginate($perPage)->appends($request->query());

            $activities = $paginator->getCollection()->map(function (Activity $activity) use ($user, $favoriteIds) {
                return $activity->toApiArray($user, in_array($activity->id, $favoriteIds, true));
            });

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'activity_type' => 'training',
                    'activities' => $activities->values(),
                    'location_types' => Activity::LOCATIONS,
                    'filters' => [
                        'q' => $validated['q'] ?? null,
                        'location_type' => $validated['location_type'] ?? null,
                        'category' => $validated['category'] ?? null,
                    ],
                    'meta' => [
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total(),
                    ],
                    'diary_note_id' => $diaryNoteId,
                ]);
            }

            $viewActivities = $paginator->getCollection();
            foreach ($viewActivities as $activity) {
                $activity->is_favorite = in_array($activity->id, $favoriteIds, true);
            }

            return view('diary.activity', ['activities' => $viewActivities]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unknown activityType',
        ], 422);
    }

    public function toggleFavorite(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'training_id' => 'required|integer',
            'is_favorite' => 'required',
        ]);

        $activity = Activity::visibleTo($user)->find($validated['training_id']);
        if (!$activity) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Activity not found',
                ], 404);
            }
            abort(404, 'Activity not found');
        }

        $isFavorite = filter_var($validated['is_favorite'], FILTER_VALIDATE_BOOLEAN);

        if ($isFavorite == false) {
            UserFavoriteActivity::firstOrCreate([
                'user_id' => $user->id,
                'activity_id' => $activity->id,
            ]);
        } else {
            UserFavoriteActivity::where([
                'user_id' => $user->id,
                'activity_id' => $activity->id,
            ])->delete();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $isFavorite ? 'Activity removed from favorites' : 'Activity added to favorites',
                'is_favorite' => !$isFavorite,
            ]);
        }
        return redirect()->route('activity');
    }

    public function updateSteps(Request $request)
    {
        $diaryNoteId = $request->expectsJson() ? $request->get('diary_note_id') : session('diary_note_id');

        if (!$diaryNoteId && $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'diary_note_id is required',
            ], 422);
        }

        $stepsCount = $request->get('stepsCount');
        $diaryNote = DiaryNote::find($diaryNoteId);

        if (!$diaryNote) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Diary note not found',
                ], 404);
            }
            abort(404, 'Diary note not found');
        }

        $diaryNote->update([
            'current_steps' => $stepsCount,
        ]);
        $burnedCalories = $diaryNote->recountBurnedCalories();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Steps updated successfully',
                'burned_calories' => $burnedCalories,
            ]);
        }

        return redirect()->route('diary');
    }
}
