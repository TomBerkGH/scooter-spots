<?php

use App\Data\UpdateSpotData;
use App\Enums\SpotImageType;

test('it creates typed update spot data from validated request values', function () {
    $data = UpdateSpotData::fromArray([
        'title' => 'Nieuwe plek',
        'description' => 'Notitie',
        'tags' => ['2', 4],
        'images' => ['view' => 'data:image/png;base64,aW1hZ2U='],
    ]);

    expect($data->title)->toBe('Nieuwe plek')
        ->and($data->description)->toBe('Notitie')
        ->and($data->tagIds)->toBe([2, 4])
        ->and($data->images[0]->type)->toBe(SpotImageType::View)
        ->and($data->images[0]->image)->toBe('data:image/png;base64,aW1hZ2U=');
});

test('it supplies nullable defaults for optional update values', function () {
    $data = UpdateSpotData::fromArray([
    ]);

    expect($data->title)->toBeNull()
        ->and($data->description)->toBeNull()
        ->and($data->tagIds)->toBe([])
        ->and($data->images)->toBe([]);
});
