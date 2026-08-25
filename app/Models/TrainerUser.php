<?php

namespace App\Models;

use App\Models\Concerns\HasModeration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class TrainerUser extends Authenticatable
{
    use HasFactory, HasApiTokens, HasModeration;
    
    protected $table = 'trainer_users';
    
    protected $fillable = [
        'email',
        'password',
        'name', 
        'surname',
        'birthday',
        'experience',
        'achievements',
        'rating',
        'rating_count',
        'moderation_status',
        'moderated_at',
        'moderation_comment',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'birthday' => 'date',
        'moderated_at' => 'datetime',
    ];

    public function trainings()
    {
        return $this->hasMany(Training::class, 'trainer_user_id');
    }
}
