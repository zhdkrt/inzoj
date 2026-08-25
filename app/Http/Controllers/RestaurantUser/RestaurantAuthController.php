<?php

namespace App\Http\Controllers\RestaurantUser;

use App\Models\RestaurantUser;
use App\Models\Restaurants\Restaurant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RestaurantAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
                'unique:restaurant_users,email',
                'unique:trainer_users,email',
                'unique:moderator_users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'restaurant_name' => 'required|string|max:255',
            'restaurant_type' => 'required|in:cafe,canteen,fine_restaurant,fast_food,bistro,pub,bar,other',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = DB::transaction(function () use ($request) {
            $restaurant = Restaurant::create([
                'name' => $request->restaurant_name,
                'restaurant_type' => $request->restaurant_type,
                'moderation_status' => Restaurant::MODERATION_PENDING,
            ]);

            return RestaurantUser::create([
                'name' => $request->name ?: $request->restaurant_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'restaurant_id' => $restaurant->id,
            ]);
        });

        $user->load('restaurant');
        $token = $user->createToken('restaurant_auth_token')->plainTextToken;
        Auth::guard('restaurant')->login($user);

        return response()->json([
            'success' => true,
            'token' => $token,
            'moderation_status' => $user->restaurant->moderation_status,
            'message' => 'Registration successful. Restaurant is pending moderation.',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'restaurant_id' => $user->restaurant_id,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }
        
        if (Auth::guard('restaurant')->attempt($request->only('email', 'password'))) {
            $user = Auth::guard('restaurant')->user();
            
            $user->tokens()->delete();
            $token = $user->createToken('restaurant_auth_token')->plainTextToken;
            $user->load('restaurant');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'moderation_status' => $user->restaurant?->moderation_status,
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'name' => $user->name ?? null,
                    ]
                ]);
            }

            $request->session()->regenerate();
            return redirect()->route('restaurantUser.menu');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный email или пароль.'
            ], 401);
        }

        return back()->withErrors([
            'email' => 'Неверный email или пароль.',
        ])->onlyInput('email');
    }
    
    public function logout(Request $request)
    {
        if ($request->expectsJson()) {
            if ($request->user('sanctum')) {
                $request->user('sanctum')->currentAccessToken()->delete();
            }
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        }
        
        if (Auth::guard('restaurant')->check()) {
            $user = Auth::guard('restaurant')->user();
            $user->tokens()->delete();
        }
                
        Auth::guard('restaurant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('restaurantUser.login');
    }
}
