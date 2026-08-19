<?php

namespace App\Actions\Spots;

use App\Models\Spot;
use App\Models\User;
use App\Services\SpotImageStorage;
use Illuminate\Auth\Access\AuthorizationException;

class DeleteSpotAction
{
    public function __construct(private readonly SpotImageStorage $images) {}

    /** @throws AuthorizationException */
    public function execute(User $user, Spot $spot): void
    {
        if ($spot->user_id !== $user->id) {
            throw new AuthorizationException;
        }

        $imagePath = $spot->image_path;
        $spot->delete();
        $this->images->delete($imagePath);
    }
}
