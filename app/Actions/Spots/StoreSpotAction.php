<?php

namespace App\Actions\Spots;

use App\Contracts\EnrichesSpotLocation;
use App\Data\StoreSpotData;
use App\Enums\SpotImageType;
use App\Models\Spot;
use App\Models\User;
use App\Services\SpotImageStorage;
use Illuminate\Support\Facades\DB;
use Throwable;

class StoreSpotAction
{
    public function __construct(
        private readonly SpotImageStorage $images,
        private readonly EnrichesSpotLocation $locationEnricher,
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(User $user, StoreSpotData $data): Spot
    {
        $imagePath = $this->images->storeDataUrl($data->image);

        try {
            $spot = DB::transaction(function () use ($user, $data, $imagePath): Spot {
                $spot = $user->spots()->create([
                    'title' => $data->title,
                    'description' => $data->description,
                    'latitude' => $data->latitude,
                    'longitude' => $data->longitude,
                ]);

                $spot->images()->create([
                    'type' => SpotImageType::Main->value,
                    'path' => $imagePath,
                ]);

                $spot->tags()->sync($data->tagIds);

                return $spot;
            });
        } catch (Throwable $exception) {
            $this->images->delete($imagePath);

            throw $exception;
        }

        $this->locationEnricher->enrich($spot);

        return $spot;
    }
}
