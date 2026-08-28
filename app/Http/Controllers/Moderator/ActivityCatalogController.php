<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityCatalogController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:100',
            'location_type' => 'nullable|in:'.implode(',', Activity::LOCATIONS),
            'category' => 'nullable|string|max:50',
        ]);

        $query = Activity::query()->whereNull('user_id')->orderBy('name');

        if (!empty($validated['q'])) {
            $query->where('name', 'like', '%'.$validated['q'].'%');
        }
        if (!empty($validated['location_type'])) {
            $query->where('location_type', $validated['location_type']);
        }
        if (!empty($validated['category'])) {
            $query->where('category', $validated['category']);
        }

        $activities = $query->get()->map(function (Activity $activity) {
            return $this->catalogArray($activity);
        });

        return response()->json([
            'success' => true,
            'activities' => $activities->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->rules($request);
        $payload = $this->payload($validated);

        $existing = Activity::whereNull('user_id')->where('name', $payload['name'])->first();
        if ($existing) {
            $existing->update($payload);
            $activity = $existing->fresh();
            $created = false;
        } else {
            $activity = Activity::create($payload);
            $created = true;
        }

        return response()->json([
            'success' => true,
            'message' => $created ? 'Activity created' : 'Activity updated',
            'activity' => $this->catalogArray($activity),
        ], $created ? 201 : 200);
    }

    public function update(Request $request, $id)
    {
        $activity = Activity::whereNull('user_id')->find($id);
        if (!$activity) {
            return response()->json([
                'success' => false,
                'message' => 'Activity not found',
            ], 404);
        }

        $validated = $this->rules($request, false);
        $activity->update($this->payload($validated, $activity));

        return response()->json([
            'success' => true,
            'message' => 'Activity updated',
            'activity' => $this->catalogArray($activity->fresh()),
        ]);
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'activities' => 'required|array|min:1|max:500',
            'activities.*.name' => 'required|string|max:255',
            'activities.*.time' => 'nullable|string|max:50',
            'activities.*.calories' => 'nullable|integer|min:0|max:5000',
            'activities.*.location_type' => 'nullable|in:'.implode(',', Activity::LOCATIONS),
            'activities.*.category' => 'nullable|string|max:50',
            'activities.*.video_url' => 'nullable|string|max:1000',
            'activities.*.is_premium' => 'nullable|boolean',
        ]);

        $created = 0;
        $updated = 0;

        foreach ($validated['activities'] as $row) {
            $payload = $this->payload($row);
            $item = Activity::whereNull('user_id')->where('name', $payload['name'])->first();
            if ($item) {
                $item->update($payload);
                $updated++;
            } else {
                Activity::create($payload);
                $created++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Catalog imported',
            'created' => $created,
            'updated' => $updated,
        ]);
    }

    private function rules(Request $request, bool $creating = true): array
    {
        return $request->validate([
            'name' => ($creating ? 'required' : 'sometimes').'|string|max:255',
            'time' => 'nullable|string|max:50',
            'calories' => 'nullable|integer|min:0|max:5000',
            'location_type' => 'nullable|in:'.implode(',', Activity::LOCATIONS),
            'category' => 'nullable|string|max:50',
            'video_url' => 'nullable|string|max:1000',
            'is_premium' => 'nullable|boolean',
        ]);
    }

    private function payload(array $validated, ?Activity $existing = null): array
    {
        return [
            'user_id' => null,
            'name' => $validated['name'] ?? $existing?->name,
            'time' => $validated['time'] ?? $existing?->time ?? '10 мин',
            'calories' => $validated['calories'] ?? $existing?->calories ?? 50,
            'location_type' => $validated['location_type'] ?? $existing?->location_type ?? Activity::LOCATION_ANY,
            'category' => array_key_exists('category', $validated)
                ? $validated['category']
                : $existing?->category,
            'video_url' => array_key_exists('video_url', $validated)
                ? $validated['video_url']
                : $existing?->video_url,
            'is_premium' => array_key_exists('is_premium', $validated)
                ? (bool) $validated['is_premium']
                : (bool) ($existing?->is_premium ?? false),
        ];
    }

    private function catalogArray(Activity $activity): array
    {
        return [
            'id' => $activity->id,
            'name' => $activity->name,
            'time' => $activity->time,
            'calories' => $activity->calories,
            'category' => $activity->category,
            'location_type' => $activity->location_type,
            'video_url' => $activity->video_url,
            'is_premium' => (bool) $activity->is_premium,
            'has_video' => filled($activity->video_url),
        ];
    }
}
