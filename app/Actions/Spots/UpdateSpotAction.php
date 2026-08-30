<?php

namespace App\Actions\Spots;

use App\Data\UpdateSpotData;
use App\Models\Spot;
use App\Services\SpotImageStorage;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateSpotAction
{
    public function __construct(private readonly SpotImageStorage $images) {}

    /** @throws Throwable */
    public function execute(Spot $spot, UpdateSpotData $data): Spot
    {
        /** @var list<string> $storedPaths */
        $storedPaths = [];
        /** @var list<string> $replacedPaths */
        $replacedPaths = [];

        try {
            foreach ($data->images as $imageData) {
                $path = $this->images->storeDataUrl($imageData->image);
                $storedPaths[] = $path;
            }

            DB::transaction(function () use ($spot, $data, $storedPaths, &$replacedPaths): void {
                $spot->update([
                    'title' => $data->title,
                    'description' => $data->description,
                ]);

                $spot->tags()->sync($data->tagIds);

                foreach ($data->images as $index => $imageData) {
                    $existingImage = $spot->images()
                        ->where('type', $imageData->type->value)
                        ->first();

                    if ($existingImage === null) {
                        $spot->images()->create([
                            'type' => $imageData->type->value,
                            'path' => $storedPaths[$index],
                        ]);
                    } else {
                        $replacedPaths[] = $existingImage->path;
                        $existingImage->update(['path' => $storedPaths[$index]]);
                    }
                }
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                $this->images->delete($path);
            }

            throw $exception;
        }

        foreach ($replacedPaths as $path) {
            $this->images->delete($path);
        }

        return $spot->fresh(['images']) ?? $spot;
    }
}
