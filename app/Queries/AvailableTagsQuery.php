<?php

namespace App\Queries;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class AvailableTagsQuery
{
    /** @return Collection<int, Tag> */
    public function execute(): Collection
    {
        return Tag::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
