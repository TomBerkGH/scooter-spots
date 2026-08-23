<?php

use App\Data\UpdateSpotData;

test('it creates typed update spot data from validated request values', function () {
    $data = UpdateSpotData::fromArray([
        'title' => 'Nieuwe plek',
        'description' => 'Notitie',
        'tags' => ['2', 4],
    ]);

    expect($data->title)->toBe('Nieuwe plek')
        ->and($data->description)->toBe('Notitie')
        ->and($data->tagIds)->toBe([2, 4]);
});

test('it supplies nullable defaults for optional update values', function () {
    $data = UpdateSpotData::fromArray([
        'title' => 'Nieuwe plek',
    ]);

    expect($data->description)->toBeNull()
        ->and($data->tagIds)->toBe([]);
});
