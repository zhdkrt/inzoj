<?php

namespace App\Http\Controllers\TrainerUser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Models\TrainerUser; 
use App\Models\Trainer; 


class TrainerAuthController extends Controller
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
            ]
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = TrainerUser::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'moderation_status' => TrainerUser::MODERATION_PENDING,
        ]);

        // Auth::guard('trainer')->attempt($request->only('email', 'password'));
        Auth::guard('trainer')->login($user);
        $token = $user->createToken('trainer_auth_token')->plainTextToken;

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'token' => $token,
                'moderation_status' => $user->moderation_status,
                'message' => 'Registration successful. Account is pending moderation.',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ]
            ]);
        }

        return redirect()->route('trainerUser.changeData');
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
        
        if (Auth::guard('trainer')->attempt($request->only('email', 'password'))) {
            $user = Auth::guard('trainer')->user();
            
            $user->tokens()->delete();
            $token = $user->createToken('trainer_auth_token')->plainTextToken;

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'moderation_status' => $user->moderation_status,
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'name' => $user->name,
                    ]
                ]);
            }

            $request->session()->regenerate();
            return redirect()->route('trainerUser.profile');
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

        if (Auth::guard('trainer')->check()) {
            $user = Auth::guard('trainer')->user();
            $user->tokens()->delete();
        }        
        Auth::guard('trainer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('trainerUser.login');
    }
}
