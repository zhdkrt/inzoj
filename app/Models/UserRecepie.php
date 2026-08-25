<?php

namespace App\Models;

use App\Models\Concerns\HasModeration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRecepie extends Model
{
    use HasFactory, HasModeration;

    protected $fillable = [
        'user_id',
        'name',
        'instructions',
        'calories',
        'proteins',
        'fats',
        'carbs',
        'moderation_status',
        'moderated_at',
        'moderation_comment',
    ];

    protected $casts = [
        'moderated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ingredients()
    {
        return $this->hasMany(UserRecepieIngridient::class);
    }
}
