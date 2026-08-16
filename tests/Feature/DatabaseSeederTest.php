<?php

use App\Models\Spot;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('r2');
});

test('seeder creates the two scooter spots accounts', function () {
    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseHas('users', [
        'name' => 'Tom',
        'email' => 'tom@scooterspots.nl',
    ]);
    $this->assertDatabaseHas('users', [
        'name' => 'Loes',
        'email' => 'loes@scooterspots.nl',
    ]);
    expect(Spot::query()->count())->toBe(4);
    Storage::disk('r2')->assertExists(Spot::query()->pluck('image_path')->all());
});

test('seeded account can log in through fortify', function () {
    $this->seed(DatabaseSeeder::class);

    $this->post(route('login.store'), [
        'email' => 'tom@scooterspots.nl',
        'password' => 'scooter',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('database seeder can run repeatedly without duplicate spots', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(Spot::query()->count())->toBe(4);
});
