<?php

namespace App\Models\Restaurants;

use App\Models\Concerns\HasModeration;
use App\Models\RestaurantUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory, HasModeration;

    protected $fillable = [
        'name',
        'restaurant_type',
        'moderation_status',
        'moderated_at',
        'moderation_comment',
    ];

    protected $casts = [
        'moderated_at' => 'datetime',
    ];

    public function restaurantUser()
    {
        return $this->hasOne(RestaurantUser::class);
    }

    public function dishes()
    {
        return $this->hasMany(Dish::class);
    }
}
