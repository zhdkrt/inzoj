<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\NutritionCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PremiumController extends Controller
{
    private $plans = [
        'quarterly' => 90,
        'yearly' => 365
    ];

    public function show(Request $request) {
        return view('profile.premium');
    }

    public function purchase(Request $request)
    {
        $validated = $request->validate([
            'plan' => 'required|in:quarterly,yearly'
        ]);

        $user = Auth::user();
        $plan = $request->input('plan');


        if ($user->is_premium && $user->premium_until > now()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Premium subscription is already active',
                    'premium_until' => $user->premium_until
                ], 400);
            }            
            return back()->with('error', 'У вас уже активна премиум подписка!');
        }

        // Обработка "платежа" (заглушка)
        $paymentSuccess = $this->processPayment($plan);
        
        if ($paymentSuccess) {
            $days = $this->plans[$plan];
            $premiumUntil = Carbon::now()->addDays($days);
            
            $user->update([
                'is_premium' => true,
                'premium_until' => $premiumUntil
            ]);
            NutritionCalculator::applyToUser($user->fresh());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Premium subscription activated successfully',
                    'plan' => $plan,
                    'premium_until' => $premiumUntil
                ]);
            }            

            return redirect()->route('profile');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed'
            ], 500);
        }

        return back()->with('error', 'Ошибка при обработке платежа');
    }

    private function processPayment(string $plan): bool
    {
        return true;
    }
}
