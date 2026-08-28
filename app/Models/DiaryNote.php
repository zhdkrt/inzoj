<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiaryNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'diary_date',
        'current_calories',
        'burned_calories',
        'current_proteins',
        'current_fats',
        'current_carbs',
        'current_water',
        'current_steps',
    ];

    public static function caloriesFromSteps($steps): float
    {
        return round(((int) $steps) * 50 / 1000, 1);
    }

    public function recountBurnedCalories(): float
    {
        $fromSteps = self::caloriesFromSteps($this->current_steps);
        $fromWorkouts = (float) UserActivity::where('diary_note_id', $this->id)->sum('calories');
        $total = round($fromSteps + $fromWorkouts, 1);

        $this->update(['burned_calories' => $total]);

        return $total;
    }

    public function userActivities()
    {
        return $this->hasMany(UserActivity::class);
    }
}
