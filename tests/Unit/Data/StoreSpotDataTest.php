<?php

use App\Data\StoreSpotData;

test('it creates typed spot data from validated request values', function () {
    $data = StoreSpotData::fromArray([
        'title' => 'Testplek',
        'description' => 'Notitie',
        'latitude' => '52.09',
        'longitude' => '5.12',
        'image' => 'data:image/png;base64,aW1hZ2U=',
        'tags' => ['2', 4],
    ]);

    expect($data->title)->toBe('Testplek')
        ->and($data->latitude)->toBe(52.09)
        ->and($data->longitude)->toBe(5.12)
        ->and($data->tagIds)->toBe([2, 4]);
});

test('it supplies nullable defaults for optional values', function () {
    $data = StoreSpotData::fromArray([
        'title' => 'Testplek',
        'image' => 'data:image/png;base64,aW1hZ2U=',
    ]);

    expect($data->description)->toBeNull()
        ->and($data->latitude)->toBeNull()
        ->and($data->longitude)->toBeNull()
        ->and($data->tagIds)->toBe([]);
});
