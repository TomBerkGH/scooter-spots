<?php

namespace Database\Factories;

use App\Models\Spot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpotFactory extends Factory
{
    protected $model = Spot::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'image_path' => 'spots/example.jpg',
        ];
    }
}
