<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FoodRecommendations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RationController extends Controller
{
    const ALLERGIES_SEPARATOR = '|';
    
    public static function getAllAllergies(): array
        {
            return [
                'lactose' => 'Непереносимость лактоза',
                'gluten' => 'Непереносимость глютена',
                'wheat' => 'Непереносимость пшеницы',
                'nuts' => 'Аллергия на орехи',
                'crayfish' => 'Аллергия на ракообразных',
                'eggs' => 'Аллергия на яйца',
                'fish' => 'Аллергия на рыбу',
                'milk' => 'Аллергия на молоко',
                'berries' => 'Аллергия на ягоды',
            ];
        }

    private function getAllergiesArray(?string $allergiesString): array
    {
        if (empty($allergiesString)) {
            return [];
        }
        
        return explode(self::ALLERGIES_SEPARATOR, $allergiesString);
    }
    
    private function getAllergiesString(array $allergies): string
    {
        return implode(self::ALLERGIES_SEPARATOR, $allergies);
    }
    
    private function userHasAllergy(User $user, string $allergy): bool
    {
        $allergies = $this->getAllergiesArray($user->allergies);
        return in_array($allergy, $allergies);
    }
    
    /**
     * Обработать кастомные аллергии из текста
     */
    private function parseCustomAllergies(string $text): array
    {
        return array_map('trim', 
            array_filter(
                explode(',', $text),
                function($item) { return !empty(trim($item)); }
            )
        );
    }
    
    /**
     * Получить только кастомные аллергии (не из стандартного списка)
     */
    private function getCustomAllergies(User $user): array
    {
        $allAllergies = $this->getAllergiesArray($user->allergies);
        $standardAllergies = array_keys($this->getAllAllergies());
        
        return array_filter($allAllergies, function($allergy) use ($standardAllergies) {
            return !in_array($allergy, $standardAllergies);
        });
    }
    
    /**
     * Получить текст кастомных аллергий для textarea
     */
    private function getCustomAllergiesText(User $user): string
    {
        $customAllergies = $this->getCustomAllergies($user);
        return implode(', ', $customAllergies);
    }

    public function show(Request $request)
    {
        $user = Auth::user();
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'user' => $user,
                'all_allergies' => $this->getAllAllergies(),
                'food_preferences' => $user->food_preferences,
                'userAllergies' => $this->getAllergiesArray($user->allergies),
                'product_recommendations' => FoodRecommendations::forUser($user->food_preferences, $user->allergies),
            ]);
        }

        return view('profile.ration', [
            'user' => $user,
            'all_allergies' => $this->getAllAllergies(),
            'food_preferences' => $user->food_preferences,
            'userAllergies' => $this->getAllergiesArray($user->allergies),
            // 'customAllergiesText' => $this->getCustomAllergiesText($user),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'allergies' => 'nullable|array',
            'allergies.*' => 'string|in:' . implode(',', array_keys($this->getAllAllergies())),
            'food_preferences' => 'string|in:no_preferences,vegan,vegetarian',
            // 'custom_allergies' => 'nullable|string|max:500',
        ]);
        
        // Стандартные аллергии из чекбоксов
        $standardAllergies = $validated['allergies'] ?? [];
        $new_food_preferences = $validated['food_preferences'];
        
        // Кастомные аллергии из текстового поля
        $customAllergies = $this->parseCustomAllergies($validated['custom_allergies'] ?? '');
        
        // Объединяем все аллергии
        $allAllergies = array_merge($standardAllergies, $customAllergies);
        
        // Преобразуем в строку и сохраняем
        $user->update([
            'allergies' => $this->getAllergiesString($allAllergies),
            'food_preferences' => $new_food_preferences
        ]);

        $user = $user->fresh();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Данные обновлены',
                '_csrf_token' => csrf_token(),
                'product_recommendations' => FoodRecommendations::forUser($user->food_preferences, $user->allergies),
            ]);
        }

        return redirect()->route('ration');
    }
}
