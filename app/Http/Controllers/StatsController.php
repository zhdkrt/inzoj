<?php

namespace App\Http\Controllers;

use App\Models\BodyLog;
use App\Models\DiaryNote;
use App\Models\ProgressPhoto;
use App\Services\NutritionCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StatsController extends Controller
{
    public function summary(Request $request)
    {
        $user = Auth::user();
        $weightLogs = BodyLog::where('user_id', $user->id)
            ->where('type', BodyLog::TYPE_WEIGHT)
            ->orderBy('logged_at')
            ->get();

        $start = $weightLogs->first();
        $current = $weightLogs->last();
        $startWeight = $start?->value ?? $user->current_weight;
        $currentWeight = $current?->value ?? $user->current_weight;

        $payload = [
            'success' => true,
            'is_premium' => $user->isPremiumActive(),
            'weight' => [
                'start' => $startWeight,
                'current' => $currentWeight,
                'target' => $user->target_weight,
                'delta' => ($startWeight !== null && $currentWeight !== null)
                    ? round($currentWeight - $startWeight, 2)
                    : null,
                'start_date' => $start?->logged_at?->toDateString(),
                'current_date' => $current?->logged_at?->toDateString(),
                'unit' => 'kg',
            ],
            'calories_today' => $this->todayCalories($user->id),
        ];

        if ($user->isPremiumActive()) {
            $photos = ProgressPhoto::where('user_id', $user->id)->orderBy('taken_at')->get();
            $payload['photos'] = [
                'count' => $photos->count(),
                'first' => $photos->first(),
                'last' => $photos->last(),
            ];
        }

        return response()->json($payload);
    }

    public function series(Request $request)
    {
        $validated = $request->validate([
            'metric' => 'required|string',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'group' => 'nullable|in:day,week',
        ]);

        $metric = $validated['metric'];
        $from = Carbon::parse($validated['from'] ?? now()->subDays(90))->startOfDay();
        $to = Carbon::parse($validated['to'] ?? now())->endOfDay();
        $group = $validated['group'] ?? 'day';

        $user = Auth::user();

        if (BodyLog::isPremiumType($metric) && !$user->isPremiumActive()) {
            return $this->premiumRequired();
        }

        if (BodyLog::isDiaryMetric($metric)) {
            $points = $this->diarySeries($user->id, $metric, $from, $to, $group);
        } elseif (in_array($metric, BodyLog::logTypes(), true)) {
            $points = $this->logSeries($user->id, $metric, $from, $to, $group);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unknown metric',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'metric' => $metric,
            'unit' => BodyLog::UNITS[$metric] ?? null,
            'group' => $group,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'points' => $points,
        ]);
    }

    public function logs(Request $request)
    {
        $validated = $request->validate([
            'type' => 'nullable|in:'.implode(',', BodyLog::logTypes()),
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $type = $validated['type'] ?? BodyLog::TYPE_WEIGHT;
        $user = Auth::user();

        if (BodyLog::isPremiumType($type) && !$user->isPremiumActive()) {
            return $this->premiumRequired();
        }

        $query = BodyLog::where('user_id', $user->id)->where('type', $type)->orderBy('logged_at');

        if (!empty($validated['from'])) {
            $query->whereDate('logged_at', '>=', $validated['from']);
        }
        if (!empty($validated['to'])) {
            $query->whereDate('logged_at', '<=', $validated['to']);
        }

        return response()->json([
            'success' => true,
            'type' => $type,
            'logs' => $query->get(),
        ]);
    }

    public function storeLog(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:'.implode(',', BodyLog::logTypes()),
            'value' => 'required|numeric',
            'logged_at' => 'nullable|date',
            'note' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        if (BodyLog::isPremiumType($validated['type']) && !$user->isPremiumActive()) {
            return $this->premiumRequired();
        }

        $rangeError = $this->valueOutOfRange($validated['type'], (float) $validated['value']);
        if ($rangeError) {
            return response()->json([
                'success' => false,
                'message' => $rangeError,
            ], 422);
        }

        $log = BodyLog::record(
            $user,
            $validated['type'],
            $validated['value'],
            $validated['logged_at'] ?? now(),
            $validated['note'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Log saved',
            'log' => $log,
        ], 201);
    }

    public function deleteLog(Request $request, $id)
    {
        $user = Auth::user();
        $log = BodyLog::where('user_id', $user->id)->find($id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Log not found',
            ], 404);
        }

        $type = $log->type;
        $log->delete();

        if ($type === BodyLog::TYPE_WEIGHT) {
            $latest = BodyLog::where('user_id', $user->id)
                ->where('type', BodyLog::TYPE_WEIGHT)
                ->orderByDesc('logged_at')
                ->first();
            $user->update(['current_weight' => $latest ? (int) round($latest->value) : null]);
        }

        if (in_array($type, [BodyLog::TYPE_WEIGHT, 'waist', 'hips', 'neck', 'chest'], true)) {
            NutritionCalculator::applyToUser($user->fresh());
        }

        return response()->json([
            'success' => true,
            'message' => 'Log deleted',
        ]);
    }

    public function compare(Request $request)
    {
        $user = Auth::user();
        $weightLogs = BodyLog::where('user_id', $user->id)
            ->where('type', BodyLog::TYPE_WEIGHT)
            ->orderBy('logged_at')
            ->get();

        $startWeight = $weightLogs->first();
        $currentWeight = $weightLogs->last();

        $result = [
            'success' => true,
            'start' => [
                'date' => $startWeight?->logged_at?->toDateString(),
                'weight' => $startWeight?->value ?? $user->current_weight,
                'photo' => null,
            ],
            'current' => [
                'date' => $currentWeight?->logged_at?->toDateString(),
                'weight' => $currentWeight?->value ?? $user->current_weight,
                'photo' => null,
            ],
            'delta_weight' => null,
        ];

        $startVal = $result['start']['weight'];
        $currentVal = $result['current']['weight'];
        if ($startVal !== null && $currentVal !== null) {
            $result['delta_weight'] = round($currentVal - $startVal, 2);
        }

        if ($user->isPremiumActive()) {
            $photos = ProgressPhoto::where('user_id', $user->id)->orderBy('taken_at')->orderBy('id')->get();
            $result['start']['photo'] = $photos->first();
            $result['current']['photo'] = $photos->last();
        }

        return response()->json($result);
    }

    public function photos(Request $request)
    {
        $user = Auth::user();
        if (!$user->isPremiumActive()) {
            return $this->premiumRequired();
        }

        $photos = ProgressPhoto::where('user_id', $user->id)
            ->orderByDesc('taken_at')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'photos' => $photos,
        ]);
    }

    public function storePhoto(Request $request)
    {
        $user = Auth::user();
        if (!$user->isPremiumActive()) {
            return $this->premiumRequired();
        }

        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
            'kind' => 'nullable|in:before,after,progress',
            'taken_at' => 'nullable|date',
            'note' => 'nullable|string|max:500',
            'weight' => 'nullable|numeric|min:30|max:300',
        ]);

        $count = ProgressPhoto::where('user_id', $user->id)->count();
        if ($count >= 50) {
            return response()->json([
                'success' => false,
                'message' => 'Photo limit reached (50)',
            ], 422);
        }

        $path = $request->file('photo')->store('progress-photos/'.$user->id, 'local');
        $takenAt = $validated['taken_at'] ?? now()->toDateString();
        $weight = $validated['weight'] ?? null;

        if ($weight !== null) {
            BodyLog::record($user, BodyLog::TYPE_WEIGHT, $weight, $takenAt);
        } else {
            $weight = BodyLog::where('user_id', $user->id)
                ->where('type', BodyLog::TYPE_WEIGHT)
                ->whereDate('logged_at', '<=', $takenAt)
                ->orderByDesc('logged_at')
                ->value('value') ?? $user->current_weight;
        }

        $photo = ProgressPhoto::create([
            'user_id' => $user->id,
            'path' => $path,
            'kind' => $validated['kind'] ?? 'progress',
            'taken_at' => $takenAt,
            'weight_at_time' => $weight,
            'note' => $validated['note'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Photo saved',
            'photo' => $photo,
        ], 201);
    }

    public function showFile(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isPremiumActive()) {
            return $this->premiumRequired();
        }

        $photo = ProgressPhoto::where('user_id', $user->id)->find($id);
        if (!$photo || !Storage::disk('local')->exists($photo->path)) {
            return response()->json([
                'success' => false,
                'message' => 'Photo not found',
            ], 404);
        }

        return Storage::disk('local')->response($photo->path);
    }

    public function deletePhoto(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isPremiumActive()) {
            return $this->premiumRequired();
        }

        $photo = ProgressPhoto::where('user_id', $user->id)->find($id);
        if (!$photo) {
            return response()->json([
                'success' => false,
                'message' => 'Photo not found',
            ], 404);
        }

        Storage::disk('local')->delete($photo->path);
        $photo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Photo deleted',
        ]);
    }

    private function diarySeries(int $userId, string $metric, Carbon $from, Carbon $to, string $group): array
    {
        $column = BodyLog::DIARY_METRICS[$metric];
        $notes = DiaryNote::where('user_id', $userId)
            ->whereDate('diary_date', '>=', $from->toDateString())
            ->whereDate('diary_date', '<=', $to->toDateString())
            ->orderBy('diary_date')
            ->get();

        $rows = $notes->map(function ($note) use ($column) {
            return [
                'date' => Carbon::parse($note->diary_date)->toDateString(),
                'value' => $note->{$column} !== null ? (float) $note->{$column} : 0,
            ];
        });

        return $this->groupPoints($rows, $group, $metric === 'weight' ? 'last' : 'sum');
    }

    private function logSeries(int $userId, string $type, Carbon $from, Carbon $to, string $group): array
    {
        $logs = BodyLog::where('user_id', $userId)
            ->where('type', $type)
            ->whereDate('logged_at', '>=', $from->toDateString())
            ->whereDate('logged_at', '<=', $to->toDateString())
            ->orderBy('logged_at')
            ->get();

        $rows = $logs->map(function ($log) {
            return [
                'date' => $log->logged_at->toDateString(),
                'value' => (float) $log->value,
            ];
        });

        return $this->groupPoints($rows, $group, 'last');
    }

    private function groupPoints($rows, string $group, string $mode): array
    {
        if ($group !== 'week') {
            return $rows->values()->all();
        }

        $grouped = $rows->groupBy(function ($row) {
            return Carbon::parse($row['date'])->startOfWeek()->toDateString();
        });

        return $grouped->map(function ($items, $weekStart) use ($mode) {
            $values = $items->pluck('value');
            $value = $mode === 'sum'
                ? round($values->sum(), 2)
                : (float) $items->last()['value'];

            return [
                'date' => $weekStart,
                'value' => $value,
            ];
        })->values()->all();
    }

    private function todayCalories(int $userId): array
    {
        $note = DiaryNote::where('user_id', $userId)
            ->whereDate('diary_date', now()->toDateString())
            ->first();

        return [
            'eaten' => $note ? (float) $note->current_calories : 0,
            'burned' => $note ? (float) $note->burned_calories : 0,
            'proteins' => $note ? (float) $note->current_proteins : 0,
            'fats' => $note ? (float) $note->current_fats : 0,
            'carbs' => $note ? (float) $note->current_carbs : 0,
        ];
    }

    private function valueOutOfRange(string $type, float $value): ?string
    {
        $ranges = [
            'weight' => [30, 300],
            'neck' => [20, 80],
            'chest' => [40, 200],
            'waist' => [40, 200],
            'hips' => [40, 200],
            'biceps' => [15, 80],
            'glucose' => [1, 40],
            'blood_pressure_sys' => [60, 250],
            'blood_pressure_dia' => [30, 150],
        ];

        if (!isset($ranges[$type])) {
            return null;
        }

        [$min, $max] = $ranges[$type];
        if ($value < $min || $value > $max) {
            return "Value for {$type} must be between {$min} and {$max}";
        }

        return null;
    }

    private function premiumRequired()
    {
        return response()->json([
            'success' => false,
            'message' => 'Premium subscription required to access this resource',
        ], 403);
    }
}
