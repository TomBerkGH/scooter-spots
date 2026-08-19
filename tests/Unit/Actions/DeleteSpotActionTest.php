<?php

use App\Actions\Spots\DeleteSpotAction;
use App\Models\Spot;
use App\Models\User;
use App\Services\SpotImageStorage;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('an owner can delete their spot and image', function () {
    $user = User::factory()->create();
    $spot = Spot::factory()->for($user)->create(['image_path' => 'spots/owned.jpg']);
    Storage::disk('public')->put($spot->image_path, 'image');

    new DeleteSpotAction(new SpotImageStorage('public'))->execute($user, $spot);

    expect($spot->exists)->toBeFalse();
    Storage::disk('public')->assertMissing('spots/owned.jpg');
});

test('a user cannot delete another users spot', function () {
    $spot = Spot::factory()->create(['image_path' => 'spots/protected.jpg']);
    Storage::disk('public')->put($spot->image_path, 'image');

    try {
        (new DeleteSpotAction(new SpotImageStorage('public')))->execute(
            User::factory()->create(),
            $spot,
        );
    } catch (AuthorizationException) {
        expect($spot->fresh())->not->toBeNull();
        Storage::disk('public')->assertExists('spots/protected.jpg');

        return;
    }

    $this->fail('Er werd geen AuthorizationException gegooid.');
});
