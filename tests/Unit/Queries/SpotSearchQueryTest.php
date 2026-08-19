<?php

use App\Models\Spot;
use App\Models\SpotLocationData;
use App\Models\Tag;
use App\Models\User;
use App\Queries\SpotSearchQuery;

test('it normalizes and limits a search term', function () {
    $query = app(SpotSearchQuery::class);

    expect($query->normalize('  koffie  '))->toBe('koffie')
        ->and($query->normalize(str_repeat('a', 120)))->toHaveLength(100)
        ->and($query->normalize(null))->toBe('');
});

test('it searches title description tags and enriched address data', function () {
    $user = User::factory()->create();
    $tag = Tag::query()->create(['name' => 'Lunchroom', 'slug' => 'lunchroom']);
    Spot::factory()->for($user)->create(['title' => 'Mooi bankje']);
    Spot::factory()->for($user)->create([
        'title' => 'Parel',
        'description' => 'Goede koffie',
    ]);
    $tagged = Spot::factory()->for($user)->create(['title' => 'Met categorie']);
    $tagged->tags()->attach($tag);
    $located = Spot::factory()->for($user)->create(['title' => 'Met locatie']);
    SpotLocationData::query()->create([
        'spot_id' => $located->id,
        'city' => 'Utrecht',
        'raw_response' => [],
        'fetched_at' => now(),
    ]);

    $query = app(SpotSearchQuery::class);

    expect($query->execute($user, 'bankje')->pluck('title')->all())
        ->toBe(['Mooi bankje'])
        ->and($query->execute($user, 'koffie')->pluck('title')->all())
        ->toBe(['Parel'])
        ->and($query->execute($user, 'lunch')->pluck('title')->all())
        ->toBe(['Met categorie'])
        ->and($query->execute($user, 'utrecht')->pluck('title')->all())
        ->toBe(['Met locatie']);
});

test('it never returns another users spots', function () {
    $user = User::factory()->create();
    Spot::factory()->for($user)->create(['title' => 'Eigen plek']);
    Spot::factory()->create(['title' => 'Eigen plek van iemand anders']);

    $spots = app(SpotSearchQuery::class)->execute($user, 'Eigen plek');

    expect($spots)->toHaveCount(1)
        ->and($spots->first()->user_id)->toBe($user->id);
});
