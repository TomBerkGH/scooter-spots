<?php

namespace Database\Factories;

use App\Enums\SpotImageType;
use App\Models\Spot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Spot> */
class SpotFactory extends Factory
{
    protected $model = Spot::class;

    public function configure(): static
    {
        return $this->afterCreating(function (Spot $spot): void {
            $spot->images()->create([
                'type' => SpotImageType::Main->value,
                'path' => 'spots/example.jpg',
            ]);
        });
    }

    public function withMainImage(string $path): static
    {
        return $this->afterCreating(function (Spot $spot) use ($path): void {
            $spot->mainImage()->update(['path' => $path]);
        });
    }

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }
}
