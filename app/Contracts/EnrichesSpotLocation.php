<?php

namespace App\Contracts;

use App\Models\Spot;
use App\Models\SpotLocationData;

interface EnrichesSpotLocation
{
    public function enrich(Spot $spot): ?SpotLocationData;
}
