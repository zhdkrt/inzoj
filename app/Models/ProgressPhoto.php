<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'path',
        'kind',
        'taken_at',
        'weight_at_time',
        'note',
    ];

    protected $hidden = [
        'path',
    ];

    protected $appends = [
        'url',
    ];

    protected $casts = [
        'taken_at' => 'date',
        'weight_at_time' => 'float',
    ];

    public function getUrlAttribute(): ?string
    {
        return $this->id ? '/api/stats/photos/'.$this->id.'/file' : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
