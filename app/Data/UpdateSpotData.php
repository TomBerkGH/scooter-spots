<?php

namespace App\Data;

final readonly class UpdateSpotData
{
    /** @param list<int> $tagIds */
    public function __construct(
        public string $title,
        public ?string $description,
        public array $tagIds,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            title: (string) $data['title'],
            description: isset($data['description']) ? (string) $data['description'] : null,
            tagIds: array_values(array_map('intval', $data['tags'] ?? [])),
        );
    }
}
