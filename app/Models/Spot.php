<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'user_id',
    'title',
    'description',
    'latitude',
    'longitude',
    'image_path',
])]
#[Appends(['image_url', 'navigation_url'])]
class Spot extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * Eigenaar van de spot.
     */
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Gekoppelde tags/categorieën.
     */
    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /** @return HasOne<SpotLocationData, $this> */
    public function locationData(): HasOne
    {
        return $this->hasOne(SpotLocationData::class);
    }

    /**
     * Genereert de volledige publieke URL voor de afbeelding.
     */
    public function getImageUrlAttribute(): string
    {
        $disk = config('filesystems.spots_disk');

        if ($disk === 'r2') {
            return Storage::disk($disk)->temporaryUrl($this->image_path, now()->addHour());
        }

        return Storage::disk($disk)->url($this->image_path);
    }

    /**
     * Maakt een directe Google Maps navigatie-URL op basis van de GPS-coördinaten.
     */
    public function getNavigationUrlAttribute(): ?string
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        return "https://www.google.com/maps/dir/?api=1&destination=$this->latitude,$this->longitude";
    }
}
