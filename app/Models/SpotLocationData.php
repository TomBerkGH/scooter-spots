<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpotLocationData extends Model
{
    protected $table = 'spot_location_data';

    protected $guarded = [];

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

    public function spot(): BelongsTo
    {
        return $this->belongsTo(Spot::class);
    }
}
