<?php

namespace Database\Seeders;

use App\Models\ModeratorUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ModeratorUserSeeder extends Seeder
{
    public function run(): void
    {
        ModeratorUser::firstOrCreate(
            ['email' => 'moderator@gmail.com'],
            [
                'name' => 'Moderator',
                'password' => Hash::make('password'),
            ]
        );
    }
}
