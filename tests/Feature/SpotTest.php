<?php

use App\Models\Spot;
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
            ->where('spots.0.title', 'Mijn plek'));
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

test('spot with coordinates is enriched with OpenStreetMap data', function () {
    Storage::fake('public');
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

    $this->actingAs($user)
        ->post(route('spots.store'), [
            'title' => 'Plek zonder verrijking',
            'latitude' => 52.090737,
            'longitude' => 5.121420,
            'image' => testImageData(),
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
