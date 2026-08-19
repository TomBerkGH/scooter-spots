<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SpotImageStorage
{
    private readonly string $disk;

    public function __construct(?string $disk = null)
    {
        $this->disk = $disk ?? (string) config('filesystems.spots_disk');
    }

    public function storeDataUrl(string $dataUrl): string
    {
        [$metadata, $encodedImage] = explode(',', $dataUrl, 2);
        $image = base64_decode($encodedImage, true);

        if ($image === false) {
            throw ValidationException::withMessages([
                'image' => 'De foto kon niet worden verwerkt.',
            ]);
        }

        $extension = str_contains($metadata, 'image/png') ? 'png' : 'jpg';
        $path = 'spots/'.Str::uuid().'.'.$extension;

        Storage::disk($this->disk)->put($path, $image);

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path !== null) {
            Storage::disk($this->disk)->delete($path);
        }
    }
}
