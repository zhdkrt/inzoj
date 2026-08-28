<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Отжимания', 'category' => 'chest', 'location_type' => 'home', 'time' => '10 мин', 'calories' => 80],
            ['name' => 'Подтягивания', 'category' => 'back', 'location_type' => 'gym', 'time' => '10 мин', 'calories' => 90],
            ['name' => 'Косые мышцы пресса', 'category' => 'abs', 'location_type' => 'home', 'time' => '10 мин', 'calories' => 50],
            ['name' => 'Верхние мышцы пресса', 'category' => 'abs', 'location_type' => 'home', 'time' => '10 мин', 'calories' => 50],
            ['name' => 'Скручивания', 'category' => 'abs', 'location_type' => 'home', 'time' => '10 мин', 'calories' => 45],
            ['name' => 'Планка', 'category' => 'core', 'location_type' => 'home', 'time' => '5 мин', 'calories' => 30],
            ['name' => 'Подъём ног лёжа', 'category' => 'abs', 'location_type' => 'home', 'time' => '10 мин', 'calories' => 40],
            ['name' => 'Русские скручивания', 'category' => 'abs', 'location_type' => 'home', 'time' => '10 мин', 'calories' => 55],
            ['name' => 'Приседания', 'category' => 'legs', 'location_type' => 'home', 'time' => '15 мин', 'calories' => 100],
            ['name' => 'Выпады', 'category' => 'legs', 'location_type' => 'home', 'time' => '10 мин', 'calories' => 80],
            ['name' => 'Ягодичный мост', 'category' => 'legs', 'location_type' => 'home', 'time' => '10 мин', 'calories' => 60],
            ['name' => 'Берпи', 'category' => 'full_body', 'location_type' => 'home', 'time' => '10 мин', 'calories' => 120],
            ['name' => 'Скакалка', 'category' => 'cardio', 'location_type' => 'home', 'time' => '10 мин', 'calories' => 110],
            ['name' => 'Бег', 'category' => 'cardio', 'location_type' => 'outdoor', 'time' => '30 мин', 'calories' => 300],
            ['name' => 'Ходьба', 'category' => 'cardio', 'location_type' => 'outdoor', 'time' => '30 мин', 'calories' => 140],
            ['name' => 'Велосипед', 'category' => 'cardio', 'location_type' => 'outdoor', 'time' => '30 мин', 'calories' => 250],
            ['name' => 'Жим лёжа', 'category' => 'chest', 'location_type' => 'gym', 'time' => '15 мин', 'calories' => 90],
            ['name' => 'Становая тяга', 'category' => 'back', 'location_type' => 'gym', 'time' => '15 мин', 'calories' => 120],
            ['name' => 'Тяга верхнего блока', 'category' => 'back', 'location_type' => 'gym', 'time' => '15 мин', 'calories' => 80],
            ['name' => 'Отжимания на брусьях', 'category' => 'arms', 'location_type' => 'gym', 'time' => '10 мин', 'calories' => 85],
            ['name' => 'Гиперэкстензия', 'category' => 'back', 'location_type' => 'gym', 'time' => '10 мин', 'calories' => 50],
        ];

        foreach ($items as $item) {
            Activity::firstOrCreate(
                [
                    'name' => $item['name'],
                    'user_id' => null,
                ],
                array_merge($item, [
                    'video_url' => null,
                    'is_premium' => false,
                ])
            );
        }
    }
}
