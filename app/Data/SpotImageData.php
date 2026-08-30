<?php

namespace App\Data;

use App\Enums\SpotImageType;

final readonly class SpotImageData
{
    public function __construct(
        public SpotImageType $type,
        public string $image,
    ) {}
}
