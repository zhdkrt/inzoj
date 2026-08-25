<?php

namespace App\Http\Controllers\Restaurant;

use App\Models\Restaurants\Restaurant; 
use App\Models\Restaurants\Dish; 

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestaurantController extends Controller
{
    public function show(Request $request) {
        $restaurantType = $request->get('restaurant_type');
        $query = Restaurant::approved();
        if (!empty($restaurantType)) {
            $query->where('restaurant_type', $restaurantType);
        }
        $correctRestaurants = $query->limit(16)->get();
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'restaurants' => $correctRestaurants,
                'filters' => [
                    'restaurant_type' => $restaurantType
                ]
            ]);
        }
        return view('restaurant.index', ['restaurants' => $correctRestaurants]);
    }

    public function restaurantMenu(Request $request) {
        $restaurantId = $request->get('restaurant_id');
        $restaurant = Restaurant::approved()->find($restaurantId);

        if (!$restaurant && $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found'
            ], 404);
        }

        $restaurantDishes = Dish::where('restaurant_id', $restaurantId)->limit(16)->get();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'restaurant' => $restaurant,
                'dishes' => $restaurantDishes
            ]);
        }

        return view('restaurant.menu', ['restaurant' => $restaurant, 'dishes' => $restaurantDishes]);
    }
}
