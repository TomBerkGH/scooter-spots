<?php

use App\Services\NominatimLocationDataMapper;

test('it maps nominatim fields and preserves the raw response', function () {
    $response = [
        'place_id' => 123,
        'osm_id' => 456,
        'osm_type' => 'way',
        'category' => 'amenity',
        'type' => 'cafe',
        'lat' => '52.09',
        'lon' => '5.12',
        'display_name' => 'Oudegracht 1, Utrecht',
        'address' => [
            'road' => 'Oudegracht',
            'house_number' => '1',
            'city' => 'Utrecht',
            'country_code' => 'nl',
        ],
        'extratags' => ['wheelchair' => 'yes'],
        'geojson' => ['type' => 'Point', 'coordinates' => [5.12, 52.09]],
    ];

    $mapped = app(NominatimLocationDataMapper::class)->map($response);

    expect($mapped)
        ->road->toBe('Oudegracht')
        ->house_number->toBe('1')
        ->city->toBe('Utrecht')
        ->osm_class->toBe('amenity')
        ->place_type->toBe('cafe')
        ->extra_tags->toBe(['wheelchair' => 'yes'])
        ->raw_response->toBe($response)
        ->fetched_at->not->toBeNull();
});

test('it safely maps a response without optional address fields', function () {
    $mapped = app(NominatimLocationDataMapper::class)->map(['address' => []]);

    expect($mapped['road'])->toBeNull()
        ->and($mapped['city'])->toBeNull()
        ->and($mapped['raw_response'])->toBe(['address' => []]);
});
