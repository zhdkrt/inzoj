<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'diary_note_id',
        'activity_id',
        'time_count',
        'time_type',
        'calories'
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
