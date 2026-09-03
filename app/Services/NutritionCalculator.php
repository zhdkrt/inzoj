<?php

namespace App\Services;

use App\Models\BodyLog;
use App\Models\User;

class NutritionCalculator
{
    public const ACTIVITY = [
        'low' => 1.2,
        'medium' => 1.55,
        'high' => 1.725,
        'expert' => 1.9,
    ];

    public const STEPS = [
        'low' => 6000,
        'medium' => 8000,
        'high' => 10000,
        'expert' => 12000,
    ];

    public const MEAL_SHARE = [
        'breakfast' => 0.25,
        'lunch' => 0.35,
        'dinner' => 0.30,
        'snack' => 0.10,
    ];

    private const GROUP_FALLBACKS = [
        'meat' => ['fish', 'eggs', 'plant_protein'],
        'fish' => ['plant_protein', 'eggs'],
        'eggs' => ['plant_protein', 'dairy'],
        'dairy' => ['plant_protein', 'nuts'],
        'nuts' => ['fats', 'fruit'],
        'fats' => ['nuts'],
        'grains' => ['fruit', 'plant_protein'],
        'fruit' => ['berries', 'vegetables'],
        'berries' => ['fruit'],
        'vegetables' => ['fruit'],
        'plant_protein' => ['grains', 'nuts'],
    ];

    public static function canCalculate(User $user): bool
    {
        return $user->current_weight
            && $user->height
            && $user->age
            && $user->gender
            && $user->activity_level
            && isset(self::ACTIVITY[$user->activity_level]);
    }

    public static function applyToUser(User $user): User
    {
        $plan = self::build($user);
        if (!($plan['ready'] ?? false)) {
            return $user;
        }

        $daily = $plan['limits']['daily'];
        $user->update([
            'calories' => $daily['calories'],
            'proteins' => $daily['proteins'],
            'fats' => $daily['fats'],
            'carbs' => $daily['carbs'],
            'water' => $daily['water'],
            'steps' => $daily['steps'],
        ]);

        return $user->fresh();
    }

    public static function build(User $user): array
    {
        $measurements = self::measurements($user);
        $bmi = self::bmi($user, $measurements);

        if (!self::canCalculate($user)) {
            return [
                'ready' => false,
                'message' => 'Fill weight, height, age, gender and activity level to calculate nutrition',
                'bmi' => $bmi,
                'calculator' => null,
                'limits' => null,
                'weekly_regime' => null,
                'product_recommendations' => FoodRecommendations::forUser($user->food_preferences, $user->allergies),
                'health' => self::healthContext($user),
            ];
        }

        $free = self::mifflin($user);
        $extended = $user->isPremiumActive()
            ? self::extended($user, $free, $measurements)
            : null;

        $chosen = $extended['calories_target'] ?? $free['calories_target'];
        $macros = self::macros($user, $chosen, (bool) $extended);
        $water = self::water((float) $user->current_weight);
        $steps = self::STEPS[$user->activity_level] ?? 8000;

        $daily = [
            'calories' => $macros['calories'],
            'proteins' => $macros['proteins'],
            'fats' => $macros['fats'],
            'carbs' => $macros['carbs'],
            'water' => $water,
            'steps' => $steps,
        ];

        return [
            'ready' => true,
            'goal' => $user->goal,
            'bmi' => $bmi,
            'calculator' => [
                'free' => $free,
                'extended' => $extended,
                'used' => $extended ? 'extended' : 'mifflin_st_jeor',
            ],
            'limits' => [
                'daily' => $daily,
                'weekly' => [
                    'calories' => $daily['calories'] * 7,
                    'proteins' => round($daily['proteins'] * 7, 1),
                    'fats' => round($daily['fats'] * 7, 1),
                    'carbs' => round($daily['carbs'] * 7, 1),
                    'water' => round($daily['water'] * 7, 1),
                    'steps' => $daily['steps'] * 7,
                ],
            ],
            'weekly_regime' => self::weeklyRegime($daily, $user),
            'product_recommendations' => FoodRecommendations::forUser($user->food_preferences, $user->allergies),
            'health' => self::healthContext($user),
        ];
    }

    public static function mifflin(User $user): array
    {
        $weight = (float) $user->current_weight;
        $height = (float) $user->height;
        $age = (int) $user->age;
        $bmr = 10 * $weight + 6.25 * $height - 5 * $age;
        $bmr = round($user->gender === 'male' ? $bmr + 5 : $bmr - 161, 0);
        $factor = self::ACTIVITY[$user->activity_level];
        $tdee = round($bmr * $factor, 0);
        $target = self::applyGoal($tdee, $user->goal, $user->gender);

        return [
            'method' => 'mifflin_st_jeor',
            'bmr' => $bmr,
            'activity_factor' => $factor,
            'tdee' => $tdee,
            'calories_target' => $target,
            'goal_adjustment' => self::goalLabel($user->goal),
        ];
    }

    public static function extended(User $user, array $free, array $measurements): array
    {
        $weight = (float) $user->current_weight;
        $height = (float) $user->height;
        $age = (int) $user->age;

        if ($user->gender === 'male') {
            $hb = 88.362 + 13.397 * $weight + 4.799 * $height - 5.677 * $age;
        } else {
            $hb = 447.593 + 9.247 * $weight + 3.098 * $height - 4.330 * $age;
        }
        $hb = round($hb, 0);
        $factor = self::ACTIVITY[$user->activity_level];
        $tdeeHb = round($hb * $factor, 0);
        $tdeeBlend = (int) round(($free['tdee'] + $tdeeHb) / 2);

        if (!empty($measurements['waist_to_height']) && $measurements['waist_to_height'] >= 0.5 && $user->goal === 'lose_weight') {
            $tdeeBlend = (int) round($tdeeBlend * 0.95);
        }

        $target = self::applyGoal($tdeeBlend, $user->goal, $user->gender, true);

        return [
            'method' => 'mifflin_harris_benedict_blend',
            'bmr_mifflin' => $free['bmr'],
            'bmr_harris_benedict' => $hb,
            'tdee_mifflin' => $free['tdee'],
            'tdee_harris_benedict' => $tdeeHb,
            'tdee' => $tdeeBlend,
            'calories_target' => $target,
            'goal_adjustment' => self::goalLabel($user->goal),
            'measurements_used' => array_keys(array_filter($measurements, fn ($v) => $v !== null)),
            'note' => 'Усреднение Миффлина и Харриса–Бенедикта; при талии/рост ≥ 0.5 и цели похудеть цель снижена ещё на 5%.',
        ];
    }

    public static function applyGoal(int $tdee, ?string $goal, ?string $gender, bool $premium = false): int
    {
        $lose = $premium ? 0.80 : 0.85;
        $gain = $premium ? 1.20 : 1.15;
        $target = match ($goal) {
            'lose_weight' => (int) round($tdee * $lose),
            'gain_muscle' => (int) round($tdee * $gain),
            default => $tdee,
        };

        $floor = $gender === 'female' ? 1200 : 1500;
        if ($goal === 'lose_weight' && $target < $floor) {
            $target = $floor;
        }

        return max(1000, min(5000, $target));
    }

    public static function macros(User $user, int $calories, bool $premium): array
    {
        $weight = (float) $user->current_weight;
        $proteinPerKg = match ($user->goal) {
            'lose_weight' => $premium ? 2.0 : 1.8,
            'gain_muscle' => $premium ? 2.2 : 2.0,
            default => 1.4,
        };
        $fatPercent = $user->goal === 'lose_weight' ? 0.25 : 0.30;

        $proteins = round($weight * $proteinPerKg, 1);
        $fats = round(($calories * $fatPercent) / 9, 1);
        $carbKcal = $calories - $proteins * 4 - $fats * 9;
        $carbs = round(max($carbKcal, 0) / 4, 1);

        return [
            'calories' => $calories,
            'proteins' => $proteins,
            'fats' => $fats,
            'carbs' => $carbs,
            'proportions' => [
                'proteins_percent' => (int) round(($proteins * 4 / max($calories, 1)) * 100),
                'fats_percent' => (int) round(($fats * 9 / max($calories, 1)) * 100),
                'carbs_percent' => (int) round(($carbs * 4 / max($calories, 1)) * 100),
            ],
        ];
    }

    public static function water(float $weightKg): float
    {
        return max(1.0, min(3.0, round($weightKg * 0.03, 1)));
    }

    public static function bmi(User $user, array $measurements): array
    {
        $weight = $user->current_weight;
        $height = $user->height;
        if (!$weight || !$height) {
            return [
                'value' => null,
                'classification' => null,
                'parameters_used' => [],
            ];
        }

        $heightM = $height / 100;
        $imt = round($weight / ($heightM * $heightM), 1);
        $idealBroca = (int) round($height - 100);
        $idealLorentz = $user->gender === 'male'
            ? round($height - 100 - ($height - 150) / 4, 1)
            : round($height - 100 - ($height - 150) / 2.5, 1);

        $used = ['weight', 'height'];
        $extra = [];
        if ($measurements['waist_cm'] !== null) {
            $used[] = 'waist';
            $extra['waist_cm'] = $measurements['waist_cm'];
            $extra['waist_to_height'] = $measurements['waist_to_height'];
            $extra['waist_to_height_risk'] = $measurements['waist_to_height'] >= 0.5 ? 'increased' : 'normal';
        }
        if ($measurements['hips_cm'] !== null) {
            $used[] = 'hips';
            $extra['hips_cm'] = $measurements['hips_cm'];
        }
        if ($measurements['neck_cm'] !== null) {
            $used[] = 'neck';
            $extra['neck_cm'] = $measurements['neck_cm'];
        }
        if ($measurements['chest_cm'] !== null) {
            $used[] = 'chest';
            $extra['chest_cm'] = $measurements['chest_cm'];
            $extra['pignet_index'] = round($height - ($weight + $measurements['chest_cm']), 1);
        }
        if ($measurements['waist_to_hip'] !== null) {
            $used[] = 'waist_to_hip';
            $extra['waist_to_hip'] = $measurements['waist_to_hip'];
        }
        $navy = self::navyBodyFat($user, $measurements);
        if ($navy !== null) {
            $extra['navy_body_fat_percent'] = $navy;
        }
        if ($user->age) {
            $used[] = 'age';
        }
        if ($user->gender) {
            $used[] = 'gender';
        }

        return [
            'value' => $imt,
            'classification' => self::bmiClass($imt),
            'ideal_weight_broca_kg' => $idealBroca,
            'ideal_weight_lorentz_kg' => $user->gender ? $idealLorentz : null,
            'parameters_used' => $used,
            'optional' => $extra,
        ];
    }

    public static function measurements(User $user): array
    {
        $waist = self::latestLog($user, 'waist');
        $hips = self::latestLog($user, 'hips');
        $heightM = $user->height ? $user->height / 100 : null;

        return [
            'waist_cm' => $waist,
            'hips_cm' => $hips,
            'neck_cm' => self::latestLog($user, 'neck'),
            'chest_cm' => self::latestLog($user, 'chest'),
            'waist_to_height' => ($waist && $heightM) ? round(($waist / 100) / $heightM, 3) : null,
            'waist_to_hip' => ($waist && $hips) ? round($waist / $hips, 3) : null,
        ];
    }

    public static function weeklyRegime(array $daily, User $user): array
    {
        $byGroup = FoodRecommendations::grouped($user->food_preferences, $user->allergies);
        $days = [];

        foreach (self::dayTemplates() as $weekday => $template) {
            $meals = [];
            $slot = 0;
            foreach (self::MEAL_SHARE as $meal => $share) {
                $mealKcal = (int) round($daily['calories'] * $share);
                $seed = ((int) $user->id) * 13 + $weekday * 41 + $slot * 17;
                $meals[$meal] = self::buildMeal($mealKcal, $template['meals'][$meal], $byGroup, $seed);
                $slot++;
            }

            self::scaleMealsToCalories($meals, (int) $daily['calories']);
            self::fitMealsToCalories(
                $meals,
                (int) $daily['calories'],
                $byGroup,
                ((int) $user->id) * 13 + $weekday * 41
            );
            self::stripMealItems($meals);

            $totals = self::sumMeals($meals);
            $names = [];
            foreach ($meals as $meal) {
                foreach ($meal['items'] as $item) {
                    $names[] = $item['name'];
                }
            }

            $days[] = [
                'weekday' => $weekday,
                'focus' => $template['focus'],
                'totals' => $totals,
                'suggested_products' => array_values(array_unique($names)),
                'meals' => $meals,
            ];
        }

        $proteinKcal = $daily['proteins'] * 4;
        $fatKcal = $daily['fats'] * 9;
        $carbKcal = $daily['carbs'] * 4;
        $sum = max($proteinKcal + $fatKcal + $carbKcal, 1);

        return [
            'kbju_proportions' => [
                'proteins_percent' => (int) round($proteinKcal / $sum * 100),
                'fats_percent' => (int) round($fatKcal / $sum * 100),
                'carbs_percent' => (int) round($carbKcal / $sum * 100),
            ],
            'meal_split' => [
                'breakfast' => '25%',
                'lunch' => '35%',
                'dinner' => '30%',
                'snack' => '10%',
            ],
            'days' => $days,
        ];
    }

    private static function dayTemplates(): array
    {
        return [
            1 => [
                'focus' => 'мясо, овощи, крупы',
                'meals' => [
                    'breakfast' => [
                        ['group' => 'eggs', 'share' => 0.35],
                        ['group' => 'grains', 'share' => 0.45],
                        ['group' => 'fruit', 'share' => 0.20],
                    ],
                    'lunch' => [
                        ['group' => 'meat', 'share' => 0.40],
                        ['group' => 'grains', 'share' => 0.35],
                        ['group' => 'vegetables', 'share' => 0.25],
                    ],
                    'dinner' => [
                        ['group' => 'meat', 'share' => 0.35],
                        ['group' => 'vegetables', 'share' => 0.40],
                        ['group' => 'fats', 'share' => 0.25],
                    ],
                    'snack' => [
                        ['group' => 'dairy', 'share' => 0.65],
                        ['group' => 'nuts', 'share' => 0.35],
                    ],
                ],
            ],
            2 => [
                'focus' => 'рыба, овощи, крупы',
                'meals' => [
                    'breakfast' => [
                        ['group' => 'dairy', 'share' => 0.35],
                        ['group' => 'grains', 'share' => 0.45],
                        ['group' => 'fruit', 'share' => 0.20],
                    ],
                    'lunch' => [
                        ['group' => 'fish', 'share' => 0.40],
                        ['group' => 'grains', 'share' => 0.35],
                        ['group' => 'vegetables', 'share' => 0.25],
                    ],
                    'dinner' => [
                        ['group' => 'fish', 'share' => 0.40],
                        ['group' => 'vegetables', 'share' => 0.35],
                        ['group' => 'fats', 'share' => 0.25],
                    ],
                    'snack' => [
                        ['group' => 'fruit', 'share' => 0.55],
                        ['group' => 'nuts', 'share' => 0.45],
                    ],
                ],
            ],
            3 => [
                'focus' => 'растительный белок, овощи',
                'meals' => [
                    'breakfast' => [
                        ['group' => 'grains', 'share' => 0.50],
                        ['group' => 'nuts', 'share' => 0.25],
                        ['group' => 'fruit', 'share' => 0.25],
                    ],
                    'lunch' => [
                        ['group' => 'plant_protein', 'share' => 0.40],
                        ['group' => 'grains', 'share' => 0.35],
                        ['group' => 'vegetables', 'share' => 0.25],
                    ],
                    'dinner' => [
                        ['group' => 'plant_protein', 'share' => 0.40],
                        ['group' => 'vegetables', 'share' => 0.35],
                        ['group' => 'fats', 'share' => 0.25],
                    ],
                    'snack' => [
                        ['group' => 'berries', 'share' => 0.55],
                        ['group' => 'nuts', 'share' => 0.45],
                    ],
                ],
            ],
            4 => [
                'focus' => 'мясо, овощи, кальций',
                'meals' => [
                    'breakfast' => [
                        ['group' => 'dairy', 'share' => 0.40],
                        ['group' => 'grains', 'share' => 0.40],
                        ['group' => 'fruit', 'share' => 0.20],
                    ],
                    'lunch' => [
                        ['group' => 'meat', 'share' => 0.40],
                        ['group' => 'grains', 'share' => 0.35],
                        ['group' => 'vegetables', 'share' => 0.25],
                    ],
                    'dinner' => [
                        ['group' => 'meat', 'share' => 0.35],
                        ['group' => 'vegetables', 'share' => 0.35],
                        ['group' => 'dairy', 'share' => 0.30],
                    ],
                    'snack' => [
                        ['group' => 'dairy', 'share' => 1.0],
                    ],
                ],
            ],
            5 => [
                'focus' => 'рыба, овощи, витамин D',
                'meals' => [
                    'breakfast' => [
                        ['group' => 'eggs', 'share' => 0.35],
                        ['group' => 'grains', 'share' => 0.45],
                        ['group' => 'fruit', 'share' => 0.20],
                    ],
                    'lunch' => [
                        ['group' => 'fish', 'share' => 0.40],
                        ['group' => 'grains', 'share' => 0.35],
                        ['group' => 'vegetables', 'share' => 0.25],
                    ],
                    'dinner' => [
                        ['group' => 'fish', 'share' => 0.40, 'prefer' => 'Скумбрия'],
                        ['group' => 'vegetables', 'share' => 0.35],
                        ['group' => 'fats', 'share' => 0.25, 'prefer' => 'Семена льна'],
                    ],
                    'snack' => [
                        ['group' => 'nuts', 'share' => 0.45],
                        ['group' => 'fruit', 'share' => 0.55],
                    ],
                ],
            ],
            6 => [
                'focus' => 'овощи, полезные жиры',
                'meals' => [
                    'breakfast' => [
                        ['group' => 'grains', 'share' => 0.45],
                        ['group' => 'nuts', 'share' => 0.25],
                        ['group' => 'fruit', 'share' => 0.30],
                    ],
                    'lunch' => [
                        ['group' => 'plant_protein', 'share' => 0.40],
                        ['group' => 'vegetables', 'share' => 0.30],
                        ['group' => 'grains', 'share' => 0.30],
                    ],
                    'dinner' => [
                        ['group' => 'plant_protein', 'share' => 0.35],
                        ['group' => 'vegetables', 'share' => 0.40],
                        ['group' => 'fats', 'share' => 0.25],
                    ],
                    'snack' => [
                        ['group' => 'nuts', 'share' => 0.50],
                        ['group' => 'fruit', 'share' => 0.50],
                    ],
                ],
            ],
            7 => [
                'focus' => 'яйца или альтернатива, овощи',
                'meals' => [
                    'breakfast' => [
                        ['group' => 'eggs', 'share' => 0.40],
                        ['group' => 'grains', 'share' => 0.40],
                        ['group' => 'fruit', 'share' => 0.20],
                    ],
                    'lunch' => [
                        ['group' => 'eggs', 'share' => 0.35],
                        ['group' => 'grains', 'share' => 0.35],
                        ['group' => 'vegetables', 'share' => 0.30],
                    ],
                    'dinner' => [
                        ['group' => 'plant_protein', 'share' => 0.40],
                        ['group' => 'vegetables', 'share' => 0.35],
                        ['group' => 'fats', 'share' => 0.25],
                    ],
                    'snack' => [
                        ['group' => 'dairy', 'share' => 0.60],
                        ['group' => 'fruit', 'share' => 0.40],
                    ],
                ],
            ],
        ];
    }

    private static function buildMeal(int $mealKcal, array $slots, array $byGroup, int $seed): array
    {
        $items = [];
        $used = [];
        foreach ($slots as $i => $slot) {
            $food = self::pickFood($byGroup, $slot['group'], $seed + $i * 19, $used, $slot['prefer'] ?? null);
            if (!$food) {
                continue;
            }
            $used[] = $food['name'];
            $items[] = self::portion($food, $mealKcal * $slot['share']);
        }

        return self::mealFromItems($items);
    }

    private static function pickFood(array $byGroup, string $group, int $seed, array $usedNames, ?string $prefer = null): ?array
    {
        $candidates = array_merge([$group], self::GROUP_FALLBACKS[$group] ?? []);
        if ($prefer) {
            foreach ($candidates as $candidate) {
                foreach ($byGroup[$candidate] ?? [] as $item) {
                    if ($item['name'] === $prefer && !in_array($prefer, $usedNames, true)) {
                        return $item;
                    }
                }
            }
        }
        foreach ($candidates as $candidate) {
            $pool = $byGroup[$candidate] ?? [];
            if (!$pool) {
                continue;
            }
            $fresh = array_values(array_filter($pool, fn ($item) => !in_array($item['name'], $usedNames, true)));
            if ($fresh) {
                $pool = $fresh;
            }
            return $pool[abs($seed) % count($pool)];
        }

        return null;
    }

    private static function portion(array $food, float $kcalBudget): array
    {
        [$min, $max] = FoodRecommendations::portionLimits($food);
        $per100 = max((float) $food['calories'], 1);
        $grams = (int) round($kcalBudget / $per100 * 100);
        $grams = max($min, min($max, $grams));

        return self::itemFromFood($food, $grams, $min, $max);
    }

    private static function itemFromFood(array $food, int $grams, int $min, int $max): array
    {
        $grams = max($min, min($max, max(1, $grams)));
        $ratio = $grams / 100;

        return [
            'name' => $food['name'],
            'group' => $food['group'],
            'role' => $food['role'],
            'grams' => $grams,
            'calories' => (int) round($food['calories'] * $ratio),
            'proteins' => round($food['proteins'] * $ratio, 1),
            'fats' => round($food['fats'] * $ratio, 1),
            'carbs' => round($food['carbs'] * $ratio, 1),
            '_c' => (float) $food['calories'],
            '_p' => (float) $food['proteins'],
            '_f' => (float) $food['fats'],
            '_cb' => (float) $food['carbs'],
            '_min' => $min,
            '_max' => $max,
        ];
    }

    private static function scaleMealsToCalories(array &$meals, int $targetCalories): void
    {
        for ($pass = 0; $pass < 2; $pass++) {
            $actual = self::sumMeals($meals)['calories'];
            if ($actual <= 0) {
                break;
            }
            $factor = $targetCalories / $actual;
            if (abs($factor - 1) < 0.08) {
                break;
            }
            foreach ($meals as &$meal) {
                foreach ($meal['items'] as &$item) {
                    $grams = (int) round($item['grams'] * $factor);
                    $item = self::itemFromFood([
                        'name' => $item['name'],
                        'group' => $item['group'],
                        'role' => $item['role'],
                        'calories' => $item['_c'],
                        'proteins' => $item['_p'],
                        'fats' => $item['_f'],
                        'carbs' => $item['_cb'],
                    ], $grams, $item['_min'], $item['_max']);
                }
                unset($item);
                $meal = self::mealFromItems($meal['items']);
            }
            unset($meal);
        }
    }

    private static function fitMealsToCalories(array &$meals, int $targetCalories, array $byGroup, int $seed): void
    {
        $tolerance = max(80, (int) round($targetCalories * 0.08));

        for ($i = 0; $i < 6; $i++) {
            $actual = self::sumMeals($meals)['calories'];
            $gap = $targetCalories - $actual;
            if (abs($gap) <= $tolerance) {
                return;
            }
            if ($gap > 0) {
                if (!self::addEnergy($meals, $gap, $targetCalories, $byGroup, $seed + $i * 23)) {
                    return;
                }
            } elseif (!self::cutEnergy($meals, -$gap, $targetCalories)) {
                return;
            }
        }
    }

    private static function addEnergy(array &$meals, int $gap, int $targetCalories, array $byGroup, int $seed): bool
    {
        $mealName = self::shortestMeal($meals, $targetCalories);
        if (!$mealName) {
            return false;
        }

        foreach (['grains', 'nuts', 'fats', 'dairy', 'plant_protein'] as $group) {
            foreach ($meals[$mealName]['items'] as $index => $item) {
                if (($item['group'] ?? '') !== $group || $item['grams'] >= $item['_max']) {
                    continue;
                }
                $per100 = max($item['_c'], 1);
                $roomKcal = ($item['_max'] - $item['grams']) / 100 * $per100;
                $addKcal = min($gap, $roomKcal);
                $addGrams = (int) round($addKcal / $per100 * 100);
                if ($addGrams < 1) {
                    continue;
                }
                $meals[$mealName]['items'][$index] = self::itemFromInternal($item, $item['grams'] + $addGrams);
                $meals[$mealName] = self::mealFromItems($meals[$mealName]['items']);

                return true;
            }
        }

        $used = array_column($meals[$mealName]['items'], 'name');
        foreach (['grains', 'nuts', 'fats', 'dairy', 'fruit'] as $offset => $group) {
            $food = self::pickFood($byGroup, $group, $seed + $offset, $used);
            if (!$food || in_array($food['name'], $used, true)) {
                continue;
            }
            $item = self::portion($food, (float) $gap);
            if ($item['calories'] < 20) {
                continue;
            }
            $meals[$mealName]['items'][] = $item;
            $meals[$mealName] = self::mealFromItems($meals[$mealName]['items']);

            return true;
        }

        return false;
    }

    private static function cutEnergy(array &$meals, int $excess, int $targetCalories): bool
    {
        $mealName = self::longestMeal($meals, $targetCalories);
        if (!$mealName) {
            return false;
        }

        foreach (['fats', 'nuts', 'grains', 'fruit', 'dairy'] as $group) {
            foreach ($meals[$mealName]['items'] as $index => $item) {
                if (($item['group'] ?? '') !== $group || $item['grams'] <= $item['_min']) {
                    continue;
                }
                $per100 = max($item['_c'], 1);
                $roomKcal = ($item['grams'] - $item['_min']) / 100 * $per100;
                $cutKcal = min($excess, $roomKcal);
                $cutGrams = (int) round($cutKcal / $per100 * 100);
                if ($cutGrams < 1) {
                    continue;
                }
                $meals[$mealName]['items'][$index] = self::itemFromInternal($item, $item['grams'] - $cutGrams);
                $meals[$mealName] = self::mealFromItems($meals[$mealName]['items']);

                return true;
            }
        }

        return false;
    }

    private static function shortestMeal(array $meals, int $targetCalories): ?string
    {
        $best = null;
        $bestShort = -INF;
        foreach (self::MEAL_SHARE as $name => $share) {
            if (!isset($meals[$name])) {
                continue;
            }
            $short = $targetCalories * $share - $meals[$name]['calories'];
            if ($short > $bestShort) {
                $bestShort = $short;
                $best = $name;
            }
        }

        return $best;
    }

    private static function longestMeal(array $meals, int $targetCalories): ?string
    {
        $best = null;
        $bestOver = -INF;
        foreach (self::MEAL_SHARE as $name => $share) {
            if (!isset($meals[$name])) {
                continue;
            }
            $over = $meals[$name]['calories'] - $targetCalories * $share;
            if ($over > $bestOver) {
                $bestOver = $over;
                $best = $name;
            }
        }

        return $best;
    }

    private static function itemFromInternal(array $item, int $grams): array
    {
        return self::itemFromFood([
            'name' => $item['name'],
            'group' => $item['group'],
            'role' => $item['role'],
            'calories' => $item['_c'],
            'proteins' => $item['_p'],
            'fats' => $item['_f'],
            'carbs' => $item['_cb'],
        ], $grams, $item['_min'], $item['_max']);
    }

    private static function stripMealItems(array &$meals): void
    {
        foreach ($meals as &$meal) {
            $meal['items'] = array_map([self::class, 'publicItem'], $meal['items']);
        }
        unset($meal);
    }

    private static function mealFromItems(array $items): array
    {
        $calories = 0;
        $proteins = 0;
        $fats = 0;
        $carbs = 0;
        foreach ($items as $item) {
            $calories += $item['calories'];
            $proteins += $item['proteins'];
            $fats += $item['fats'];
            $carbs += $item['carbs'];
        }

        return [
            'calories' => $calories,
            'proteins' => round($proteins, 1),
            'fats' => round($fats, 1),
            'carbs' => round($carbs, 1),
            'items' => $items,
        ];
    }

    private static function sumMeals(array $meals): array
    {
        $calories = 0;
        $proteins = 0;
        $fats = 0;
        $carbs = 0;
        foreach ($meals as $meal) {
            $calories += $meal['calories'];
            $proteins += $meal['proteins'];
            $fats += $meal['fats'];
            $carbs += $meal['carbs'];
        }

        return [
            'calories' => $calories,
            'proteins' => round($proteins, 1),
            'fats' => round($fats, 1),
            'carbs' => round($carbs, 1),
        ];
    }

    private static function publicItem(array $item): array
    {
        unset($item['_c'], $item['_p'], $item['_f'], $item['_cb'], $item['_min'], $item['_max']);

        return $item;
    }

    private static function healthContext(User $user): array
    {
        return [
            'food_preferences' => $user->food_preferences,
            'allergies' => array_values(array_filter(explode('|', (string) $user->allergies))),
        ];
    }

    private static function navyBodyFat(User $user, array $measurements): ?float
    {
        $waist = $measurements['waist_cm'];
        $neck = $measurements['neck_cm'];
        $hips = $measurements['hips_cm'];
        $height = $user->height ? (float) $user->height : null;
        if (!$waist || !$neck || !$height || $waist <= $neck) {
            return null;
        }

        if ($user->gender === 'male') {
            $den = 1.0324 - 0.19077 * log10($waist - $neck) + 0.15456 * log10($height);
        } elseif ($user->gender === 'female' && $hips) {
            $den = 1.29579 - 0.35004 * log10($waist + $hips - $neck) + 0.22100 * log10($height);
        } else {
            return null;
        }

        if ($den <= 0) {
            return null;
        }

        return round(495 / $den - 450, 1);
    }

    private static function latestLog(User $user, string $type): ?float
    {
        $value = BodyLog::where('user_id', $user->id)
            ->where('type', $type)
            ->orderByDesc('logged_at')
            ->value('value');

        return $value !== null ? (float) $value : null;
    }

    private static function bmiClass(float $imt): string
    {
        if ($imt < 18.5) {
            return 'дефицит массы тела';
        }
        if ($imt <= 24.9) {
            return 'норма';
        }
        if ($imt <= 29.9) {
            return 'избыточная масса';
        }

        return 'ожирение';
    }

    private static function goalLabel(?string $goal): string
    {
        return match ($goal) {
            'lose_weight' => 'deficit',
            'gain_muscle' => 'surplus',
            default => 'maintain',
        };
    }
}
