<?php

use App\Actions\Spots\StoreSpotAction;
use App\Contracts\EnrichesSpotLocation;
use App\Data\StoreSpotData;
use App\Models\Spot;
use App\Models\Tag;
use App\Models\User;
use App\Services\SpotImageStorage;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('it stores a spot tags and image before enriching its location', function () {
    $user = User::factory()->create();
    $tag = Tag::query()->create(['name' => 'Rustig', 'slug' => 'rustig']);
    $enricher = Mockery::mock(EnrichesSpotLocation::class);
    $enricher->shouldReceive('enrich')
        ->once()
        ->with(Mockery::type(Spot::class))
        ->andReturnNull();

    $spot = new StoreSpotAction(new SpotImageStorage('public'), $enricher)->execute(
        $user,
        new StoreSpotData(
            title: 'Testplek',
            description: 'Een omschrijving',
            latitude: 52.09,
            longitude: 5.12,
            image: 'data:image/png;base64,'.base64_encode('image'),
            tagIds: [$tag->id],
        ),
    );

    expect($spot->user_id)->toBe($user->id)
        ->and($spot->tags()->pluck('tags.id')->all())->toBe([$tag->id]);
    Storage::disk('public')->assertExists($spot->image_path);
});

test('it removes the stored image when database persistence fails', function () {
    $user = User::factory()->create();
    $enricher = Mockery::mock(EnrichesSpotLocation::class);
    $enricher->shouldNotReceive('enrich');
    $action = new StoreSpotAction(new SpotImageStorage('public'), $enricher);

    try {
        $action->execute($user, new StoreSpotData(
            title: 'Mislukte plek',
            description: null,
            latitude: null,
            longitude: null,
            image: 'data:image/png;base64,'.base64_encode('image'),
            tagIds: [999999],
        ));
    } catch (QueryException) {
        // De foreign-keyfout is de verwachte aanleiding voor de rollback.
    }

    expect(Spot::query()->count())->toBe(0)
        ->and(Storage::disk('public')->allFiles('spots'))->toBe([]);
});
