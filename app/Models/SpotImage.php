<?php

namespace App\Models;

use App\Enums\SpotImageType;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'spot_id',
    'type',
    'path',
])]
#[Appends(['image_url'])]
class SpotImage extends Model
{
    protected function casts(): array
    {
        return [
            'type' => SpotImageType::class,
        ];
    }

    /**
     * De spot waaraan deze afbeelding gekoppeld is.
     */
    /** @return BelongsTo<Spot, $this> */
    public function spot(): BelongsTo
    {
        return $this->belongsTo(Spot::class);
    }

    /**
     * Genereert de volledige publieke URL voor de afbeelding.
     */
    public function getImageUrlAttribute(): string
    {
        $disk = config('filesystems.spots_disk');

        if ($disk === 'r2') {
            return Storage::disk($disk)->temporaryUrl($this->path, now()->addHour());
        }

        return Storage::disk($disk)->url($this->path);
    }
}
