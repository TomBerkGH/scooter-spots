<?php

namespace App\Actions\Spots;

use App\Data\UpdateSpotData;
use App\Models\Spot;

class UpdateSpotAction
{
    public function execute(Spot $spot, UpdateSpotData $data): Spot
    {
        $spot->update([
            'title' => $data->title,
            'description' => $data->description,
        ]);

        $spot->tags()->sync($data->tagIds);

        return $spot;
    }
}
