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

        /** @var list<string> $imagePaths */
        $imagePaths = $spot->images()->pluck('path')->all();
        $spot->delete();

        foreach ($imagePaths as $imagePath) {
            $this->images->delete($imagePath);
        }
    }
}
