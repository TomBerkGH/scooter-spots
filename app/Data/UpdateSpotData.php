<?php

namespace App\Data;

use App\Enums\SpotImageType;

final readonly class UpdateSpotData
{
    /**
     * @param list<int> $tagIds
     * @param list<SpotImageData> $images
     */
    public function __construct(
        public ?string $title,
        public ?string $description,
        public array $tagIds,
        public array $images,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        /** @var list<SpotImageData> $images */
        $images = [];

        foreach (is_array($data['images'] ?? null) ? $data['images'] : [] as $type => $image) {
            $imageType = is_string($type) ? SpotImageType::tryFrom($type) : null;

            if ($imageType !== null && is_string($image) && $image !== '') {
                $images[] = new SpotImageData(type: $imageType, image: $image);
            }
        }

        return new self(
            title: isset($data['title']) ? (string) $data['title'] : null,
            description: isset($data['description']) ? (string) $data['description'] : null,
            tagIds: array_values(array_map('intval', $data['tags'] ?? [])),
            images: $images,
        );
    }
}
