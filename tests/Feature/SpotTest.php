<?php

use App\Models\Spot;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['filesystems.spots_disk' => 'public']);
});

function testImageData(): string
{
    return 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('apple-touch-icon.png')));
}

test('guests cannot visit spots', function () {
    $this->get(route('spots.index'))->assertRedirect(route('login'));
});

test('user only sees their own spots', function () {
    $user = User::factory()->create();
    Spot::factory()->for($user)->create(['title' => 'Mijn plek']);
    Spot::factory()->create(['title' => 'Andere plek']);

    $this->actingAs($user)
        ->get(route('spots.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Spots/Index')
            ->has('spots', 1)
            ->where('spots.0.title', 'Mijn plek'));
});

test('user can store a spot with photo and location', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('spots.store'), [
            'title' => 'Mooi uitzicht',
            'description' => 'Aan het water',
            'latitude' => 52.090737,
            'longitude' => 5.121420,
            'image' => testImageData(),
        ])
        ->assertRedirect(route('spots.index'));

    $spot = Spot::firstOrFail();

    expect($spot->user_id)->toBe($user->id);
    Storage::disk('public')->assertExists($spot->image_path);
});

test('user can store a spot without location', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('spots.store'), [
            'title' => 'Plek zonder GPS',
            'image' => testImageData(),
        ])
        ->assertRedirect(route('spots.index'));

    $spot = Spot::firstOrFail();

    expect($spot->latitude)->toBeNull()
        ->and($spot->longitude)->toBeNull()
        ->and($spot->navigation_url)->toBeNull();
});

test('user cannot delete another users spot', function () {
    $user = User::factory()->create();
    $spot = Spot::factory()->create();

    $this->actingAs($user)
        ->delete(route('spots.destroy', $spot))
        ->assertForbidden();

    $this->assertDatabaseHas('spots', ['id' => $spot->id]);
});

test('owner can delete spot and photo', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    Storage::disk('public')->put('spots/photo.jpg', 'photo');
    $spot = Spot::factory()->for($user)->create(['image_path' => 'spots/photo.jpg']);

    $this->actingAs($user)
        ->delete(route('spots.destroy', $spot))
        ->assertRedirect(route('spots.index'));

    $this->assertDatabaseMissing('spots', ['id' => $spot->id]);
    Storage::disk('public')->assertMissing('spots/photo.jpg');
});
