<?php

namespace App\Data;

final readonly class StoreSpotData
{
    /** @param list<int> $tagIds */
    public function __construct(
        public string $title,
        public ?string $description,
        public ?float $latitude,
        public ?float $longitude,
        public string $image,
        public array $tagIds,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            title: (string) $data['title'],
            description: isset($data['description']) ? (string) $data['description'] : null,
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            image: (string) $data['image'],
            tagIds: array_values(array_map('intval', $data['tags'] ?? [])),
        );
    }
}
