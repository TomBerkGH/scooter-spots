<?php

namespace App\Services;

use App\Contracts\EnrichesSpotLocation;
use App\Models\Spot;
use App\Models\SpotLocationData;
use Illuminate\Support\Facades\Http;
use Throwable;

class NominatimService implements EnrichesSpotLocation
{
    public function __construct(private readonly NominatimLocationDataMapper $mapper) {}

    public function enrich(Spot $spot): ?SpotLocationData
    {
        if ($spot->latitude === null || $spot->longitude === null) {
            return null;
        }

        try {
            $request = Http::acceptJson()
                ->withHeaders([
                    'User-Agent' => config('services.nominatim.user_agent'),
                    'Referer' => config('app.url'),
                ])
                ->connectTimeout(3)
                ->timeout(8);

            $parameters = [
                'format' => 'jsonv2',
                'lat' => $spot->latitude,
                'lon' => $spot->longitude,
                'zoom' => 18,
                'addressdetails' => 1,
                'extratags' => 1,
                'namedetails' => 1,
                'polygon_geojson' => 1,
            ];

            if (config('services.nominatim.email')) {
                $parameters['email'] = config('services.nominatim.email');
            }

            $response = $request->get(
                rtrim(config('services.nominatim.url'), '/').'/reverse',
                $parameters,
            );
            $data = $response->json();

            if (
                $response->failed()
                || ! is_array($data)
                || ! is_array($data['address'] ?? null)
            ) {
                return null;
            }

            return $spot->locationData()->updateOrCreate(
                [],
                $this->mapper->map($data),
            );
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }
}
