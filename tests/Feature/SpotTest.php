<?php

use App\Enums\SpotImageType;
use App\Models\Spot;
use App\Models\SpotImage;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['filesystems.spots_disk' => 'public']);
    Http::fake([
        'http://[::1]:5173/__inertia_ssr' => Http::response([], 503),
    ]);
    Http::preventStrayRequests();
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
            ->where('spots.0.title', 'Mijn plek')
            ->where('spots.0.user.name', $user->name));
});

test('user can search their spots by title description or tag', function () {
    $user = User::factory()->create();
    $tag = Tag::query()->create(['name' => 'Natuurgebied', 'slug' => 'natuurgebied']);

    Spot::factory()->for($user)->create(['title' => 'Bankje aan de Lek']);
    Spot::factory()->for($user)->create([
        'title' => 'Verborgen parel',
        'description' => 'Heerlijke koffie en taart',
    ]);
    $taggedSpot = Spot::factory()->for($user)->create(['title' => 'De groene route']);
    $taggedSpot->tags()->attach($tag);
    Spot::factory()->for($user)->create(['title' => 'Dorpsplein']);
    Spot::factory()->create(['title' => 'Bankje van iemand anders']);

    $this->actingAs($user)
        ->get(route('spots.index', ['search' => 'bankje']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('spots', 1)
            ->where('spots.0.title', 'Bankje aan de Lek')
            ->where('filters.search', 'bankje'));

    $this->actingAs($user)
        ->get(route('spots.index', ['search' => 'koffie']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('spots', 1)
            ->where('spots.0.title', 'Verborgen parel'));

    $this->actingAs($user)
        ->get(route('spots.index', ['search' => 'natuur']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('spots', 1)
            ->where('spots.0.title', 'De groene route'));
});

test('user can store a spot with photo and location', function () {
    Storage::fake('public');
    Http::fake(['*' => Http::response([], 503)]);
    $user = User::factory()->create();
    $tag = Tag::query()->create(['name' => 'Uitzicht', 'slug' => 'uitzicht']);

    $this->actingAs($user)
        ->post(route('spots.store'), [
            'title' => 'Mooi uitzicht',
            'description' => 'Aan het water',
            'latitude' => 52.090737,
            'longitude' => 5.121420,
            'image' => testImageData(),
            'tags' => [$tag->id],
        ])
        ->assertRedirect(route('spots.index'));

    $spot = Spot::firstOrFail();

    expect($spot->user_id)->toBe($user->id);
    Storage::disk('public')->assertExists($spot->mainImage()->firstOrFail()->path);
});

test('spot with coordinates is enriched with OpenStreetMap data', function () {
    Storage::fake('public');
    $tag = Tag::query()->create(['name' => 'Rustig', 'slug' => 'rustig']);
    $user = User::factory()->create();
    Http::fake([
        '*' => Http::response([
            'place_id' => 123456,
            'osm_type' => 'way',
            'osm_id' => 987654,
            'lat' => '52.0907370',
            'lon' => '5.1214200',
            'category' => 'amenity',
            'type' => 'cafe',
            'place_rank' => 30,
            'importance' => 0.0001,
            'display_name' => '1, Oudegracht, Utrecht, 3511 AA, Nederland',
            'name' => 'Testcafé',
            'address' => [
                'house_number' => '1',
                'road' => 'Oudegracht',
                'city' => 'Utrecht',
                'municipality' => 'Utrecht',
                'state' => 'Utrecht',
                'postcode' => '3511 AA',
                'country' => 'Nederland',
                'country_code' => 'nl',
            ],
            'boundingbox' => ['52.09', '52.10', '5.12', '5.13'],
            'extratags' => ['wheelchair' => 'yes', 'website' => 'https://example.com'],
            'namedetails' => ['name' => 'Testcafé'],
            'geojson' => ['type' => 'Point', 'coordinates' => [5.121420, 52.090737]],
            'licence' => 'Data © OpenStreetMap contributors, ODbL 1.0.',
        ]),
    ]);

    $this->actingAs($user)
        ->post(route('spots.store'), [
            'title' => 'Verrijkte plek',
            'latitude' => 52.090737,
            'longitude' => 5.121420,
            'image' => testImageData(),
            'tags' => [$tag->id],
        ])
        ->assertRedirect(route('spots.index'));

    $spot = Spot::firstOrFail();
    $location = $spot->locationData()->firstOrFail();

    expect($location->road)->toBe('Oudegracht')
        ->and($location->city)->toBe('Utrecht')
        ->and($location->extra_tags)->toBe([
            'wheelchair' => 'yes',
            'website' => 'https://example.com',
        ])
        ->and($location->raw_response['place_id'])->toBe(123456);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/reverse')
        && $request['addressdetails'] === 1
        && $request['extratags'] === 1
        && $request['namedetails'] === 1
        && $request['polygon_geojson'] === 1
        && $request->hasHeader('User-Agent'));

    $this->actingAs($user)
        ->get(route('spots.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('spots.0.location_data.road', 'Oudegracht')
            ->where('spots.0.location_data.raw_response.place_id', 123456));
});

test('spot is still stored when OpenStreetMap is unavailable', function () {
    Storage::fake('public');
    Http::fake(['*' => Http::response([], 503)]);
    $user = User::factory()->create();
    $tag = Tag::query()->create(['name' => 'Rustig', 'slug' => 'rustig']);

    $this->actingAs($user)
        ->post(route('spots.store'), [
            'title' => 'Plek zonder verrijking',
            'latitude' => 52.090737,
            'longitude' => 5.121420,
            'image' => testImageData(),
            'tags' => [$tag->id],
        ])
        ->assertRedirect(route('spots.index'));

    expect(Spot::query()->count())->toBe(1)
        ->and(Spot::firstOrFail()->locationData)->toBeNull();
});

test('create form shows the available tags alphabetically', function () {
    $user = User::factory()->create();
    Tag::query()->create(['name' => 'Zonnig', 'slug' => 'zonnig']);
    Tag::query()->create(['name' => 'Rustig', 'slug' => 'rustig']);

    $this->actingAs($user)
        ->get(route('spots.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Spots/Create')
            ->has('tags', 2)
            ->where('tags.0.name', 'Rustig')
            ->where('tags.1.name', 'Zonnig'));
});

test('spots page shows the available tags alphabetically', function () {
    $user = User::factory()->create();
    Tag::query()->create(['name' => 'Zonnig', 'slug' => 'zonnig']);
    Tag::query()->create(['name' => 'Rustig', 'slug' => 'rustig']);

    $this->actingAs($user)
        ->get(route('spots.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Spots/Index')
            ->has('tags', 2)
            ->where('tags.0.name', 'Rustig')
            ->where('tags.1.name', 'Zonnig'));
});

test('user can assign existing tags to a new spot', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $selectedTag = Tag::query()->create(['name' => 'Rustig', 'slug' => 'rustig']);
    $otherTag = Tag::query()->create(['name' => 'Druk', 'slug' => 'druk']);

    $this->actingAs($user)
        ->post(route('spots.store'), [
            'title' => 'Plek met tag',
            'image' => testImageData(),
            'tags' => [$selectedTag->id],
        ])
        ->assertRedirect(route('spots.index'));

    $spot = Spot::firstOrFail();

    expect($spot->tags()->pluck('tags.id')->all())
        ->toBe([$selectedTag->id])
        ->not->toContain($otherTag->id);
});

test('user cannot assign a tag that does not exist', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('spots.store'), [
            'title' => 'Plek met ongeldige tag',
            'image' => testImageData(),
            'tags' => [999999],
        ])
        ->assertSessionHasErrors('tags.0');

    expect(Spot::query()->count())->toBe(0);
});

test('user can store a spot without location', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $tag = Tag::query()->create(['name' => 'Rustig', 'slug' => 'rustig']);

    $this->actingAs($user)
        ->post(route('spots.store'), [
            'title' => 'Plek zonder GPS',
            'image' => testImageData(),
            'tags' => [$tag->id],
        ])
        ->assertRedirect(route('spots.index'));

    $spot = Spot::firstOrFail();

    expect($spot->latitude)->toBeNull()
        ->and($spot->longitude)->toBeNull()
        ->and($spot->navigation_url)->toBeNull();
});

test('user can store a spot without a title', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $tag = Tag::query()->create(['name' => 'Rustig', 'slug' => 'rustig']);

    $this->actingAs($user)
        ->post(route('spots.store'), [
            'image' => testImageData(),
            'tags' => [$tag->id],
        ])
        ->assertRedirect(route('spots.index'));

    $this->assertDatabaseHas('spots', [
        'user_id' => $user->id,
        'title' => null,
    ]);
});

test('user cannot store a spot without a tag', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('spots.store'), [
            'image' => testImageData(),
        ])
        ->assertSessionHasErrors('tags');

    expect(Spot::query()->count())->toBe(0);
});

test('user cannot store a spot without a main image', function () {
    $user = User::factory()->create();
    $tag = Tag::query()->create(['name' => 'Rustig', 'slug' => 'rustig']);

    $this->actingAs($user)
        ->post(route('spots.store'), [
            'tags' => [$tag->id],
        ])
        ->assertSessionHasErrors('image');

    expect(Spot::query()->count())->toBe(0);
});

test('owner can update a spots title description and tags', function () {
    $user = User::factory()->create();
    $oldTag = Tag::query()->create(['name' => 'Oud', 'slug' => 'oud']);
    $newTag = Tag::query()->create(['name' => 'Uitzicht', 'slug' => 'uitzicht']);
    $spot = Spot::factory()->for($user)->create([
        'title' => 'Oude titel',
        'description' => 'Oude omschrijving',
        'latitude' => 52.09,
        'longitude' => 5.12,
    ]);
    $spot->tags()->attach($oldTag);

    $this->actingAs($user)
        ->patch(route('spots.update', $spot), [
            'title' => 'Nieuwe titel',
            'description' => 'Nieuwe omschrijving',
            'tags' => [$newTag->id],
        ])
        ->assertRedirect(route('spots.index'));

    $this->assertDatabaseHas('spots', [
        'id' => $spot->id,
        'title' => 'Nieuwe titel',
        'description' => 'Nieuwe omschrijving',
    ]);
    $updatedSpot = $spot->fresh();
    expect($updatedSpot->tags->pluck('id')->all())
        ->toBe([$newTag->id])
        ->and($updatedSpot->latitude)->toBe(52.09)
        ->and($updatedSpot->longitude)->toBe(5.12);
});

test('owner can update a spot without a title', function () {
    $user = User::factory()->create();
    $tag = Tag::query()->create(['name' => 'Rustig', 'slug' => 'rustig']);
    $spot = Spot::factory()->for($user)->create(['title' => 'Oude titel']);
    $spot->tags()->attach($tag);

    $this->actingAs($user)
        ->patch(route('spots.update', $spot), [
            'title' => null,
            'description' => $spot->description,
            'tags' => [$tag->id],
        ])
        ->assertRedirect(route('spots.index'));

    $this->assertDatabaseHas('spots', [
        'id' => $spot->id,
        'title' => null,
    ]);
});

test('owner can add an optional view image to a spot', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $tag = Tag::query()->create(['name' => 'Rustig', 'slug' => 'rustig']);
    $spot = Spot::factory()->for($user)->create();

    $this->actingAs($user)
        ->patch(route('spots.update', $spot), [
            'title' => $spot->title,
            'description' => $spot->description,
            'images' => ['view' => testImageData()],
            'tags' => [$tag->id],
        ])
        ->assertRedirect(route('spots.index'));

    $viewImage = SpotImage::query()->where('spot_id', $spot->id)->firstOrFail();

    expect($viewImage->type)->toBe(SpotImageType::View);
    Storage::disk('public')->assertExists($viewImage->path);
});

test('owner can replace an existing view image', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $tag = Tag::query()->create(['name' => 'Rustig', 'slug' => 'rustig']);
    $spot = Spot::factory()->for($user)->create();
    $oldPath = 'spots/old-view.jpg';
    Storage::disk('public')->put($oldPath, 'old image');
    $spot->images()->create(['type' => 'view', 'path' => $oldPath]);

    $this->actingAs($user)
        ->patch(route('spots.update', $spot), [
            'title' => $spot->title,
            'description' => $spot->description,
            'images' => ['view' => testImageData()],
            'tags' => [$tag->id],
        ])
        ->assertRedirect(route('spots.index'));

    $newPath = $spot->fresh()->images()->firstOrFail()->path;

    expect($newPath)->not->toBe($oldPath);
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($newPath);
});

test('owner cannot remove all tags from a spot', function () {
    $user = User::factory()->create();
    $tag = Tag::query()->create(['name' => 'Uitzicht', 'slug' => 'uitzicht']);
    $spot = Spot::factory()->for($user)->create();
    $spot->tags()->attach($tag);

    $this->actingAs($user)
        ->patch(route('spots.update', $spot), [
            'title' => $spot->title,
            'description' => $spot->description,
            'tags' => [],
        ])
        ->assertSessionHasErrors('tags');

    expect($spot->fresh()->tags)->toHaveCount(1);
});

test('user cannot update a spot with an invalid tag', function () {
    $user = User::factory()->create();
    $spot = Spot::factory()->for($user)->create([
        'title' => 'Oude titel',
        'description' => 'Oude omschrijving',
    ]);

    $this->actingAs($user)
        ->patch(route('spots.update', $spot), [
            'title' => 'Nieuwe titel',
            'description' => 'Nieuwe omschrijving',
            'tags' => [999999],
        ])
        ->assertSessionHasErrors('tags.0');

    $this->assertDatabaseHas('spots', [
        'id' => $spot->id,
        'title' => 'Oude titel',
        'description' => 'Oude omschrijving',
    ]);
});

test('user cannot update another users spot', function () {
    $user = User::factory()->create();
    $spot = Spot::factory()->create([
        'title' => 'Oude titel',
        'description' => 'Oude omschrijving',
    ]);

    $this->actingAs($user)
        ->patch(route('spots.update', $spot), [
            'title' => 'Nieuwe titel',
            'description' => 'Nieuwe omschrijving',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('spots', [
        'id' => $spot->id,
        'title' => 'Oude titel',
        'description' => 'Oude omschrijving',
    ]);
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
    $spot = Spot::factory()->for($user)->withMainImage('spots/photo.jpg')->create();

    $this->actingAs($user)
        ->delete(route('spots.destroy', $spot))
        ->assertRedirect(route('spots.index'));

    $this->assertDatabaseMissing('spots', ['id' => $spot->id]);
    Storage::disk('public')->assertMissing('spots/photo.jpg');
});

test('owner can delete a spot and its additional images', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    Storage::disk('public')->put('spots/photo.jpg', 'photo');
    Storage::disk('public')->put('spots/view.jpg', 'view');
    $spot = Spot::factory()->for($user)->withMainImage('spots/photo.jpg')->create();
    $spot->images()->create(['type' => 'view', 'path' => 'spots/view.jpg']);

    $this->actingAs($user)
        ->delete(route('spots.destroy', $spot))
        ->assertRedirect(route('spots.index'));

    Storage::disk('public')->assertMissing('spots/photo.jpg');
    Storage::disk('public')->assertMissing('spots/view.jpg');
});
