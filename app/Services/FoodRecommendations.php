<?php

namespace App\Services;

class FoodRecommendations
{
    public static function catalog(): array
    {
        return [
            ['name' => 'Горбуша', 'group' => 'fish', 'role' => 'рыба, омега-3', 'allergens' => ['fish'], 'diets' => ['omnivore'], 'calories' => 149, 'proteins' => 25.4, 'fats' => 4.4, 'carbs' => 0],
            ['name' => 'Треска', 'group' => 'fish', 'role' => 'рыба, белок', 'allergens' => ['fish'], 'diets' => ['omnivore'], 'calories' => 82, 'proteins' => 18.0, 'fats' => 0.7, 'carbs' => 0],
            ['name' => 'Креветки', 'group' => 'fish', 'role' => 'белок, йод', 'allergens' => ['fish', 'crayfish'], 'diets' => ['omnivore'], 'calories' => 99, 'proteins' => 24.0, 'fats' => 0.3, 'carbs' => 0.2],
            ['name' => 'Скумбрия', 'group' => 'fish', 'role' => 'рыба, омега-3, витамин D', 'allergens' => ['fish'], 'diets' => ['omnivore'], 'calories' => 205, 'proteins' => 19.0, 'fats' => 13.9, 'carbs' => 0],
            ['name' => 'Куриная грудка', 'group' => 'meat', 'role' => 'мясо, белок', 'allergens' => [], 'diets' => ['omnivore'], 'calories' => 165, 'proteins' => 31.0, 'fats' => 3.6, 'carbs' => 0],
            ['name' => 'Индейка', 'group' => 'meat', 'role' => 'мясо, белок', 'allergens' => [], 'diets' => ['omnivore'], 'calories' => 135, 'proteins' => 30.0, 'fats' => 0.7, 'carbs' => 0],
            ['name' => 'Говядина нежирная', 'group' => 'meat', 'role' => 'мясо, железо, B12', 'allergens' => [], 'diets' => ['omnivore'], 'calories' => 158, 'proteins' => 26.0, 'fats' => 6.0, 'carbs' => 0],
            ['name' => 'Тофу', 'group' => 'plant_protein', 'role' => 'белок, кальций', 'allergens' => [], 'diets' => ['vegetarian', 'vegan'], 'calories' => 144, 'proteins' => 15.6, 'fats' => 8.7, 'carbs' => 2.8],
            ['name' => 'Чечевица', 'group' => 'plant_protein', 'role' => 'белок, железо, фолиевая кислота', 'allergens' => [], 'diets' => ['vegetarian', 'vegan'], 'calories' => 116, 'proteins' => 9.0, 'fats' => 0.4, 'carbs' => 20.0],
            ['name' => 'Нут', 'group' => 'plant_protein', 'role' => 'белок, клетчатка', 'allergens' => [], 'diets' => ['vegetarian', 'vegan'], 'calories' => 164, 'proteins' => 8.9, 'fats' => 2.6, 'carbs' => 27.4],
            ['name' => 'Яйца', 'group' => 'eggs', 'role' => 'белок, витамин D, холин', 'allergens' => ['eggs'], 'diets' => ['omnivore', 'vegetarian'], 'calories' => 155, 'proteins' => 12.6, 'fats' => 10.6, 'carbs' => 1.1],
            ['name' => 'Творог 5%', 'group' => 'dairy', 'role' => 'кальций, белок', 'allergens' => ['milk', 'lactose'], 'diets' => ['omnivore', 'vegetarian'], 'calories' => 121, 'proteins' => 16.0, 'fats' => 5.0, 'carbs' => 3.0],
            ['name' => 'Натуральный йогурт', 'group' => 'dairy', 'role' => 'кальций, белок', 'allergens' => ['milk', 'lactose'], 'diets' => ['omnivore', 'vegetarian'], 'calories' => 68, 'proteins' => 5.0, 'fats' => 3.7, 'carbs' => 4.7],
            ['name' => 'Брокколи', 'group' => 'vegetables', 'role' => 'овощи, витамин C, K', 'allergens' => [], 'diets' => ['vegetarian', 'vegan'], 'calories' => 34, 'proteins' => 2.8, 'fats' => 0.4, 'carbs' => 6.6],
            ['name' => 'Шпинат', 'group' => 'vegetables', 'role' => 'овощи, железо, фолиевая кислота', 'allergens' => [], 'diets' => ['vegetarian', 'vegan'], 'calories' => 23, 'proteins' => 2.9, 'fats' => 0.4, 'carbs' => 3.6],
            ['name' => 'Морковь', 'group' => 'vegetables', 'role' => 'овощи, витамин A', 'allergens' => [], 'diets' => ['vegetarian', 'vegan'], 'calories' => 41, 'proteins' => 0.9, 'fats' => 0.2, 'carbs' => 9.6],
            ['name' => 'Болгарский перец', 'group' => 'vegetables', 'role' => 'овощи, витамин C', 'allergens' => [], 'diets' => ['vegetarian', 'vegan'], 'calories' => 31, 'proteins' => 1.0, 'fats' => 0.3, 'carbs' => 6.0],
            ['name' => 'Овсянка', 'group' => 'grains', 'role' => 'углеводы, клетчатка', 'allergens' => ['gluten'], 'diets' => ['vegetarian', 'vegan'], 'calories' => 379, 'proteins' => 13.2, 'fats' => 6.5, 'carbs' => 67.5, 'portion_min' => 40, 'portion_max' => 80],
            ['name' => 'Хлеб цельнозерновой', 'group' => 'grains', 'role' => 'углеводы, клетчатка, витамины группы B', 'allergens' => ['gluten', 'wheat'], 'diets' => ['vegetarian', 'vegan'], 'calories' => 247, 'proteins' => 9.0, 'fats' => 3.4, 'carbs' => 43.0, 'portion_min' => 30, 'portion_max' => 80],
            ['name' => 'Гречка', 'group' => 'grains', 'role' => 'углеводы, магний', 'allergens' => [], 'diets' => ['vegetarian', 'vegan'], 'calories' => 343, 'proteins' => 13.3, 'fats' => 3.4, 'carbs' => 71.5, 'portion_min' => 40, 'portion_max' => 90],
            ['name' => 'Киноа', 'group' => 'grains', 'role' => 'углеводы, белок', 'allergens' => [], 'diets' => ['vegetarian', 'vegan'], 'calories' => 368, 'proteins' => 14.1, 'fats' => 6.1, 'carbs' => 64.2, 'portion_min' => 40, 'portion_max' => 80],
            ['name' => 'Рис бурый', 'group' => 'grains', 'role' => 'углеводы', 'allergens' => [], 'diets' => ['vegetarian', 'vegan'], 'calories' => 362, 'proteins' => 7.5, 'fats' => 2.7, 'carbs' => 76.2, 'portion_min' => 40, 'portion_max' => 90],
            ['name' => 'Грецкий орех', 'group' => 'nuts', 'role' => 'омега-3, витамин E', 'allergens' => ['nuts'], 'diets' => ['vegetarian', 'vegan'], 'calories' => 654, 'proteins' => 15.2, 'fats' => 65.2, 'carbs' => 13.7, 'portion_min' => 15, 'portion_max' => 40],
            ['name' => 'Миндаль', 'group' => 'nuts', 'role' => 'витамин E, магний', 'allergens' => ['nuts'], 'diets' => ['vegetarian', 'vegan'], 'calories' => 579, 'proteins' => 21.2, 'fats' => 49.9, 'carbs' => 21.6, 'portion_min' => 15, 'portion_max' => 40],
            ['name' => 'Семена льна', 'group' => 'fats', 'role' => 'омега-3', 'allergens' => [], 'diets' => ['vegetarian', 'vegan'], 'calories' => 534, 'proteins' => 18.3, 'fats' => 42.2, 'carbs' => 28.9, 'portion_min' => 10, 'portion_max' => 25],
            ['name' => 'Авокадо', 'group' => 'fats', 'role' => 'полезные жиры', 'allergens' => [], 'diets' => ['vegetarian', 'vegan'], 'calories' => 160, 'proteins' => 2.0, 'fats' => 14.7, 'carbs' => 8.5, 'portion_min' => 50, 'portion_max' => 150],
            ['name' => 'Оливковое масло', 'group' => 'fats', 'role' => 'полезные жиры', 'allergens' => [], 'diets' => ['vegetarian', 'vegan'], 'calories' => 884, 'proteins' => 0, 'fats' => 100.0, 'carbs' => 0, 'portion_min' => 5, 'portion_max' => 20],
            ['name' => 'Черника', 'group' => 'berries', 'role' => 'ягоды, антиоксиданты', 'allergens' => ['berries'], 'diets' => ['vegetarian', 'vegan'], 'calories' => 57, 'proteins' => 0.7, 'fats' => 0.3, 'carbs' => 14.5],
            ['name' => 'Апельсин', 'group' => 'fruit', 'role' => 'витамин C', 'allergens' => [], 'diets' => ['vegetarian', 'vegan'], 'calories' => 47, 'proteins' => 0.9, 'fats' => 0.1, 'carbs' => 11.8],
            ['name' => 'Яблоко', 'group' => 'fruit', 'role' => 'витамин C, клетчатка', 'allergens' => [], 'diets' => ['vegetarian', 'vegan'], 'calories' => 52, 'proteins' => 0.3, 'fats' => 0.2, 'carbs' => 14.0],
        ];
    }

    public static function portionLimits(array $item): array
    {
        if (isset($item['portion_min'], $item['portion_max'])) {
            return [(int) $item['portion_min'], (int) $item['portion_max']];
        }

        return match ($item['group']) {
            'fish', 'meat' => [80, 200],
            'plant_protein' => [80, 220],
            'eggs' => [50, 150],
            'dairy' => [80, 250],
            'vegetables' => [100, 250],
            'grains' => [40, 90],
            'nuts' => [15, 40],
            'fats' => [8, 30],
            'berries' => [50, 150],
            'fruit' => [80, 200],
            default => [30, 200],
        };
    }

    public static function forUser(?string $foodPreferences, ?string $allergiesString): array
    {
        $items = [];
        foreach (self::filtered($foodPreferences, $allergiesString) as $item) {
            $items[] = [
                'name' => $item['name'],
                'group' => $item['group'],
                'role' => $item['role'],
                'calories' => $item['calories'],
                'proteins' => $item['proteins'],
                'fats' => $item['fats'],
                'carbs' => $item['carbs'],
                'per' => '100g',
            ];
        }

        return $items;
    }

    public static function filtered(?string $foodPreferences, ?string $allergiesString): array
    {
        $allergies = array_values(array_filter(explode('|', (string) $allergiesString)));
        $diet = $foodPreferences ?: 'no_preferences';

        $items = [];
        foreach (self::catalog() as $item) {
            if ($diet === 'vegan' && !in_array('vegan', $item['diets'], true)) {
                continue;
            }
            if ($diet === 'vegetarian' && !in_array('vegetarian', $item['diets'], true) && !in_array('vegan', $item['diets'], true)) {
                continue;
            }
            if ($allergies && array_intersect($allergies, $item['allergens'])) {
                continue;
            }
            $items[] = $item;
        }

        return $items;
    }

    public static function grouped(?string $foodPreferences, ?string $allergiesString): array
    {
        $byGroup = [];
        foreach (self::filtered($foodPreferences, $allergiesString) as $item) {
            $byGroup[$item['group']][] = $item;
        }

        return $byGroup;
    }
}
