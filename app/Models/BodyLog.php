<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class BodyLog extends Model
{
    use HasFactory;

    public const TYPE_WEIGHT = 'weight';

    public const FREE_TYPES = ['weight'];

    public const PREMIUM_TYPES = [
        'neck',
        'chest',
        'waist',
        'hips',
        'biceps',
        'glucose',
        'blood_pressure_sys',
        'blood_pressure_dia',
    ];

    public const DIARY_METRICS = [
        'calories' => 'current_calories',
        'burned_calories' => 'burned_calories',
        'proteins' => 'current_proteins',
        'fats' => 'current_fats',
        'carbs' => 'current_carbs',
        'water' => 'current_water',
        'steps' => 'current_steps',
    ];

    public const UNITS = [
        'weight' => 'kg',
        'neck' => 'cm',
        'chest' => 'cm',
        'waist' => 'cm',
        'hips' => 'cm',
        'biceps' => 'cm',
        'glucose' => 'mmol/l',
        'blood_pressure_sys' => 'mmHg',
        'blood_pressure_dia' => 'mmHg',
    ];

    protected $fillable = [
        'user_id',
        'type',
        'value',
        'unit',
        'logged_at',
        'note',
    ];

    protected $casts = [
        'value' => 'float',
        'logged_at' => 'date',
    ];

    public static function logTypes(): array
    {
        return array_merge(self::FREE_TYPES, self::PREMIUM_TYPES);
    }

    public static function isPremiumType(string $type): bool
    {
        return in_array($type, self::PREMIUM_TYPES, true);
    }

    public static function isDiaryMetric(string $metric): bool
    {
        return array_key_exists($metric, self::DIARY_METRICS);
    }

    public static function record(User $user, string $type, $value, $loggedAt = null, ?string $note = null): self
    {
        $date = Carbon::parse($loggedAt ?? now())->toDateString();
        $unit = self::UNITS[$type] ?? null;

        $log = static::updateOrCreate(
            [
                'user_id' => $user->id,
                'type' => $type,
                'logged_at' => $date,
            ],
            [
                'value' => $value,
                'unit' => $unit,
                'note' => $note,
            ]
        );

        if ($type === self::TYPE_WEIGHT) {
            $user->update(['current_weight' => (int) round((float) $value)]);
        }

        return $log;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
