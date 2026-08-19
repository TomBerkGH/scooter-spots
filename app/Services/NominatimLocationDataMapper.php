<?php

namespace App\Services;

class NominatimLocationDataMapper
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function map(array $data): array
    {
        $address = is_array($data['address'] ?? null) ? $data['address'] : [];

        return [
            'osm_place_id' => $data['place_id'] ?? null,
            'osm_id' => $data['osm_id'] ?? null,
            'osm_type' => $data['osm_type'] ?? null,
            'osm_class' => $data['class'] ?? $data['category'] ?? null,
            'place_type' => $data['addresstype'] ?? $data['type'] ?? null,
            'place_rank' => $data['place_rank'] ?? null,
            'importance' => $data['importance'] ?? null,
            'latitude' => $data['lat'] ?? null,
            'longitude' => $data['lon'] ?? null,
            'display_name' => $data['display_name'] ?? null,
            'name' => $data['name'] ?? null,
            'house_number' => $address['house_number'] ?? null,
            'road' => $address['road'] ?? null,
            'neighbourhood' => $address['neighbourhood'] ?? null,
            'suburb' => $address['suburb'] ?? null,
            'city_district' => $address['city_district'] ?? null,
            'city' => $address['city'] ?? null,
            'town' => $address['town'] ?? null,
            'village' => $address['village'] ?? null,
            'municipality' => $address['municipality'] ?? null,
            'county' => $address['county'] ?? null,
            'state_district' => $address['state_district'] ?? null,
            'state' => $address['state'] ?? null,
            'region' => $address['region'] ?? null,
            'postcode' => $address['postcode'] ?? null,
            'country' => $address['country'] ?? null,
            'country_code' => $address['country_code'] ?? null,
            'bounding_box' => $data['boundingbox'] ?? null,
            'address' => $address,
            'extra_tags' => $data['extratags'] ?? null,
            'name_details' => $data['namedetails'] ?? null,
            'geometry' => $data['geojson'] ?? null,
            'raw_response' => $data,
            'license' => $data['licence'] ?? null,
            'fetched_at' => now(),
        ];
    }
}
