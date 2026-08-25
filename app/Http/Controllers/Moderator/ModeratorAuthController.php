<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\ModeratorUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ModeratorAuthController extends Controller
{
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
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $moderator = ModeratorUser::where('email', $request->email)->first();

        if (!$moderator || !Hash::check($request->password, $moderator->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Неверный email или пароль.',
                ], 401);
            }

            return back()->withErrors([
                'email' => 'Неверный email или пароль.',
            ])->onlyInput('email');
        }

        if ($request->expectsJson()) {
            $moderator->tokens()->delete();
            $token = $moderator->createToken('moderator_auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $moderator->id,
                    'email' => $moderator->email,
                    'name' => $moderator->name,
                ],
            ]);
        }

        Auth::guard('moderator')->login($moderator);
        $request->session()->regenerate();

        return redirect()->route('moderator.queue');
    }

    public function logout(Request $request)
    {
        if ($request->expectsJson()) {
            if ($request->user('sanctum')) {
                $request->user('sanctum')->currentAccessToken()->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
        }

        Auth::guard('moderator')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('moderator.login');
    }
}
