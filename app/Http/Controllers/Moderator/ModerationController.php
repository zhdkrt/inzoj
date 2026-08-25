<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Restaurants\Restaurant;
use App\Models\TrainerUser;
use App\Models\UserRecepie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function queue(Request $request)
    {
        $pending = [
            'recipes' => UserRecepie::pending()->count(),
            'restaurants' => Restaurant::pending()->count(),
            'trainers' => TrainerUser::pending()->count(),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'pending' => $pending,
            ]);
        }

        return view('moderator.queue', [
            'pending' => $pending,
            'recipes' => UserRecepie::pending()->with('user:id,name,email')->latest()->limit(5)->get(),
            'restaurants' => Restaurant::pending()->with('restaurantUser:id,name,email,restaurant_id')->latest()->limit(5)->get(),
            'trainers' => TrainerUser::pending()->latest()->limit(5)->get(),
        ]);
    }

    public function recipes(Request $request)
    {
        $recipes = $this->filtered(UserRecepie::query()->with('user:id,name,email'), $request)
            ->latest()
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'recipes' => $recipes,
            ]);
        }

        return view('moderator.recipes', [
            'recipes' => $recipes,
            'status' => $this->status($request),
        ]);
    }

    public function showRecipe(Request $request, $id)
    {
        $recipe = UserRecepie::with(['user:id,name,email', 'ingredients'])->find($id);

        if (!$recipe) {
            return $this->notFound($request, 'Recipe not found');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'recipe' => $recipe,
            ]);
        }

        return view('moderator.recipe', compact('recipe'));
    }

    public function reviewRecipe(Request $request, $id)
    {
        $recipe = UserRecepie::find($id);

        if (!$recipe) {
            return $this->notFound($request, 'Recipe not found');
        }

        return $this->review($recipe, $request);
    }

    public function restaurants(Request $request)
    {
        $restaurants = $this->filtered(
            Restaurant::query()->with('restaurantUser:id,name,email,restaurant_id'),
            $request
        )->latest()->get();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'restaurants' => $restaurants,
            ]);
        }

        return view('moderator.restaurants', [
            'restaurants' => $restaurants,
            'status' => $this->status($request),
        ]);
    }

    public function showRestaurant(Request $request, $id)
    {
        $restaurant = Restaurant::with(['restaurantUser:id,name,email,restaurant_id', 'dishes'])->find($id);

        if (!$restaurant) {
            return $this->notFound($request, 'Restaurant not found');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'restaurant' => $restaurant,
            ]);
        }

        return view('moderator.restaurant', compact('restaurant'));
    }

    public function reviewRestaurant(Request $request, $id)
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return $this->notFound($request, 'Restaurant not found');
        }

        return $this->review($restaurant, $request);
    }

    public function trainers(Request $request)
    {
        $trainers = $this->filtered(TrainerUser::query(), $request)->latest()->get();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'trainers' => $trainers,
            ]);
        }

        return view('moderator.trainers', [
            'trainers' => $trainers,
            'status' => $this->status($request),
        ]);
    }

    public function showTrainer(Request $request, $id)
    {
        $trainer = TrainerUser::with('trainings')->find($id);

        if (!$trainer) {
            return $this->notFound($request, 'Trainer not found');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'trainer' => $trainer,
            ]);
        }

        return view('moderator.trainer', compact('trainer'));
    }

    public function reviewTrainer(Request $request, $id)
    {
        $trainer = TrainerUser::find($id);

        if (!$trainer) {
            return $this->notFound($request, 'Trainer not found');
        }

        return $this->review($trainer, $request);
    }

    private function status(Request $request): string
    {
        $status = $request->get('status', 'pending');

        return in_array($status, ['pending', 'approved', 'rejected', 'all'], true)
            ? $status
            : 'pending';
    }

    private function filtered($query, Request $request)
    {
        $status = $this->status($request);

        if ($status !== 'all') {
            $query->where('moderation_status', $status);
        }

        return $query;
    }

    private function review(Model $model, Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'comment' => 'nullable|string|max:1000',
        ]);

        $model->moderate($validated['action'], $validated['comment'] ?? null);
        $approved = $validated['action'] === 'approve';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $approved ? 'Approved' : 'Rejected',
                'item' => $model->fresh(),
            ]);
        }

        return back()->with('success', $approved ? 'Заявка одобрена' : 'Заявка отклонена');
    }

    private function notFound(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 404);
        }

        abort(404, $message);
    }
}
