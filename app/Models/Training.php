<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    public function userTraining() {
        return $this->hasMany(UserTraining::class, 'training_id', 'id');
    }

    public function trainerUser() {
        return $this->belongsTo(TrainerUser::class, 'trainer_user_id');
    }

    protected $fillable = [
        'trainer_user_id',
        'name', 
        'time_amount',
        'description',
        'price',
        'start_time',
        'date',
        'category_id'
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'date' => 'date:d.m'
    ];
}
