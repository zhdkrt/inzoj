<?php

namespace App\Http\Controllers\Diary;

use App\Models\Product;
use App\Models\UserFavoriteProduct;
use App\Models\UserFavoriteRecepie;
use App\Models\UserRecepie;

use App\Models\Recepies\Recepie;
use App\Models\Recepies\RecepieMealType;
use App\Models\Recepies\RecepieComponent;
use App\Models\Recepies\RecepieCookingMethod;
use App\Models\Recepies\RecepieDiet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MealController extends Controller
{
    public function show (Request $request) {
        $user = Auth::user();

        $mealType = $request->get('meal_type');
        if (!$request->expectsJson() && isset($mealType)) {
            session(['meal_type' => $mealType]);
        }
        
        $productsOrRecepies = $request->get('productsOrRecepies') ?? 'products';

        if ($productsOrRecepies == 'products') {
            $favoriteProductsId = UserFavoriteProduct::where('user_id', $user->id)
                ->pluck('product_id')
                ->toArray();
            if (!empty($favoriteProductsId)) {
                $favorites = Product::whereIn('id', $favoriteProductsId)->limit(16)->get();
                $others = Product::whereNotIn('id', $favoriteProductsId)->limit(16 - $favorites->count())->get();
                $products = $favorites->concat($others);
            } else {
                $products = Product::limit(16)->get();
            }
            
            foreach ($products as $product) {
                if (in_array($product->id, $favoriteProductsId)) {
                    $product->is_favorite = true;
                } else {
                    $product->is_favorite = false;
                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'products' => $products,
                    'recepies' => null,
                    'meal_type' => $mealType
                ]);
            }

            return view('diary.meal', ['products' => $products, 'recepies' => null]);
        } elseif ($productsOrRecepies == 'recepies') {
            $favoriteRecepiesId = UserFavoriteRecepie::where('user_id', $user->id)
                ->pluck('recepie_id')
                ->toArray();
            if (!empty($favoriteRecepiesId)) {
                $favorites = Recepie::whereIn('id', $favoriteRecepiesId)->limit(16)->get();
                $others = Recepie::whereNotIn('id', $favoriteRecepiesId)->limit(16 - $favorites->count())->get();
                $recepies = $favorites->concat($others);
            } else {
                $recepies = Recepie::limit(16)->get();
            }
            $userRecepies = UserRecepie::where('user_id', $user->id)->get();
            $publicUserRecepies = UserRecepie::approved()
                ->where('user_id', '!=', $user->id)
                ->limit(16)
                ->get();

            $userRecepies->each(function ($recepie) {
                $recepie->setAttribute('is_user_recepie', true);
            });
            $publicUserRecepies->each(function ($recepie) {
                $recepie->setAttribute('is_user_recepie', true);
            });

            $allRecepies = $userRecepies->concat($publicUserRecepies)->concat($recepies);

            foreach ($allRecepies as $recepie) {
                if (in_array($recepie->id, $favoriteRecepiesId)) {
                    $recepie->is_favorite = true;
                } else {
                    $recepie->is_favorite = false;
                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'products' => null,
                    'recepies' => $allRecepies,
                    'meal_type' => $mealType
                ]);
            }

            return view('diary.meal', ['products' => null, 'recepies' => $allRecepies]);            
        }
    }

    public function toggleFavorite(Request $request) {
        $userId = Auth::user()->id;
        $productsOrRecepies = $request->get('productsOrRecepies');
        $isFavorite = $request->get('is_favorite');

        if ($productsOrRecepies == 'products') {
            $productId = $request->get('product_id');
            if ($isFavorite == false) {
                UserFavoriteProduct::create([
                    'user_id' => $userId,
                    'product_id' => $productId
                ]);
            } elseif ($isFavorite == true) {
                UserFavoriteProduct::where([
                    'user_id' => $userId,
                    'product_id' => $productId
                ])->delete();    
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $isFavorite ? 'Product removed from favorites' : 'Product added to favorites',
                    'is_favorite' => !$isFavorite
                ]);
            }
            return redirect()->route('meal');

        } elseif ($productsOrRecepies == 'recepies') {
            $recepieId = $request->get('recepie_id');
            if ($isFavorite == false) {
                UserFavoriteRecepie::create([
                    'user_id' => $userId,
                    'recepie_id' => $recepieId
                ]);
            } elseif ($isFavorite == true) {
                UserFavoriteRecepie::where([
                    'user_id' => $userId,
                    'recepie_id' => $recepieId
                ])->delete();    
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $isFavorite ? 'Recepie removed from favorites' : 'Recepie added to favorites',
                    'is_favorite' => !$isFavorite
                ]);
            }
            return redirect()->route('meal');
        }
        return redirect()->route('meal');
    }

    public function showFilter(Request $request) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'filters' => [
                    'meal_types' => ['breakfast', 'lunch', 'dinner', 'snack'],
                    'components' => ['poultry', 'meat', 'fish', 'vegetables', 'fruits', 'sweet'],
                    'cooking_methods' => ['boiled', 'steamed', 'fried', 'stew', 'baked', 'basic'],
                    'diets' => ['vegetarian', 'vegan', 'low_fat', 'lots_of_fiber', 'low_carb', 'keto_diet', 'high_protein', 'lactose_free']
                ]
            ]);
        }
        return view('diary.filters');
    }

    public function filterApply(Request $request) {
        $mealType = $request->get('meal_type', []);
        if (!is_array($mealType)) {
            $mealType = [$mealType];
        }
        $component = $request->get('component', []);
        if (!is_array($component)) {
            $component = [$component];
        }
        $cookingMethod = $request->get('cooking_method', []);
        if (!is_array($cookingMethod)) {
            $cookingMethod = [$cookingMethod];
        }
        $diet = $request->get('diet', []);
        if (!is_array($diet)) {
            $diet = [$diet];
        }

        $allFilters = [
            RecepieMealType::class => [$mealType, 'meal_type'],
            RecepieComponent::class => [$component, 'component'],
            RecepieCookingMethod::class => [$cookingMethod, 'cooking_method'],
            RecepieDiet::class => [$diet, 'diet']
        ];

        $correctIds = [];
        foreach ($allFilters as $modelClass => [$filterType, $fieldName]) {
            if ($filterType) {
                foreach ($filterType as $filter) {
                    $newIds = $modelClass::where($fieldName, $filter)
                        ->pluck('recepie_id')
                        ->toArray();

                    $correctIds = array_merge($correctIds, $newIds);
                }
            }
        }
        
        $uniqueIds = array_unique($correctIds);

        if (empty($uniqueIds) && empty($mealType) && empty($component) && empty($cookingMethod) && empty($diet)) {
            $recepies = Recepie::limit(6)->get();
        } else {
            $recepies = Recepie::whereIn('id', $uniqueIds)->limit(6)->get();
        }
        
        if (count($recepies) == 0) {
            $recepies = null;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'products' => null,
                'recepies' => $recepies,
                'meal_type' => $mealType,
                'component' => $component,
                'cooking_method' => $cookingMethod,
                'diet' => $diet
            ]);
        }

        return view('diary.meal', [
            'products' => null,
            'recepies' => $recepies,
            'mealType' => $mealType,
            'component' => $component,
            'cookingMethod' => $cookingMethod,
            'diet' => $diet
        ]);
    }
}
