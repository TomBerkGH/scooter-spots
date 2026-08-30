<?php

namespace App\Models;

use App\Enums\SpotImageType;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'title',
    'description',
    'latitude',
    'longitude',
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

    /** @return HasMany<SpotImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(SpotImage::class);
    }

    /** @return HasOne<SpotImage, $this> */
    public function mainImage(): HasOne
    {
        return $this->hasOne(SpotImage::class)
            ->where('type', SpotImageType::Main->value);
    }

    /**
     * Genereert de volledige publieke URL voor de afbeelding.
     */
    public function getImageUrlAttribute(): string
    {
        $mainImage = $this->relationLoaded('images')
            ? $this->images->first(
                fn (SpotImage $image): bool => $image->type === SpotImageType::Main,
            )
            : $this->mainImage()->firstOrFail();

        if ($mainImage === null) {
            $mainImage = $this->mainImage()->firstOrFail();
        }

        return $mainImage->image_url;
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
