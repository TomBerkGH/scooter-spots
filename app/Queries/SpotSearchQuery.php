<?php

namespace App\Queries;

use App\Models\Spot;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SpotSearchQuery
{
    public function normalize(?string $search): string
    {
        return mb_substr(trim((string) $search), 0, 100);
    }

    /** @return Collection<int, Spot> */
    public function execute(User $user, string $search = ''): Collection
    {
        return $user->spots()
            ->with(['tags:id,name', 'locationData'])
            ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search))
            ->latest()
            ->get();
    }

    /** @param Builder<Spot> $query */
    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('tags', fn (Builder $query) => $query
                    ->where('name', 'like', "%{$search}%"))
                ->orWhereHas('locationData', function (Builder $query) use ($search) {
                    $query->where('display_name', 'like', "%{$search}%")
                        ->orWhere('road', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('town', 'like', "%{$search}%")
                        ->orWhere('village', 'like', "%{$search}%")
                        ->orWhere('municipality', 'like', "%{$search}%")
                        ->orWhere('postcode', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%");
                });
        });
    }
}
