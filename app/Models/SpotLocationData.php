<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'spot_location_data')]
#[Unguarded]
class SpotLocationData extends Model
{
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'importance' => 'float',
            'bounding_box' => 'array',
            'address' => 'array',
            'extra_tags' => 'array',
            'name_details' => 'array',
            'geometry' => 'array',
            'raw_response' => 'array',
            'fetched_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Spot, $this> */
    public function spot(): BelongsTo
    {
        return $this->belongsTo(Spot::class);
    }
}
