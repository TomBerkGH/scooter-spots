<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Tom', 'Loes'] as $name) {
            User::query()->updateOrCreate(
                ['email' => strtolower($name).'@scooterspots.nl'],
                [
                    'name' => $name,
                    'password' => 'scooter',
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
