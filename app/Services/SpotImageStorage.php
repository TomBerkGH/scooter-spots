<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SpotImageStorage
{
    /** @var array<string, string> */
    private const EXTENSIONS_BY_MIME_TYPE = [
        'image/apng' => 'apng',
        'image/avif' => 'avif',
        'image/bmp' => 'bmp',
        'image/gif' => 'gif',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/svg+xml' => 'svg',
        'image/vnd.microsoft.icon' => 'ico',
        'image/webp' => 'webp',
        'image/x-icon' => 'ico',
    ];

    private readonly string $disk;

    public function __construct(?string $disk = null)
    {
        $this->disk = $disk ?? (string) config('filesystems.spots_disk');
    }

    public function storeDataUrl(string $dataUrl): string
    {
        $parts = explode(',', $dataUrl, 2);

        if (count($parts) !== 2 || ! preg_match('/^data:image\/[^;,]+(?:;[^,]*)*;base64$/i', trim($parts[0]))) {
            throw $this->invalidImageException();
        }

        $metadata = strtolower(trim($parts[0]));
        $mimeType = explode(';', substr($metadata, 5), 2)[0];
        $extension = self::EXTENSIONS_BY_MIME_TYPE[$mimeType] ?? null;

        if ($extension === null) {
            throw $this->invalidImageException();
        }

        $encodedImage = $parts[1];
        $image = base64_decode($encodedImage, true);

        if ($image === false || $image === '') {
            throw $this->invalidImageException();
        }

        $prefix = app()->environment('local') ? 'dev/' : 'spots/';

        $path = $prefix.Str::uuid().'.'.$extension;

        Storage::disk($this->disk)->put($path, $image);

        return $path;
    }

    private function invalidImageException(): ValidationException
    {
        return ValidationException::withMessages([
            'image' => 'De foto kon niet worden verwerkt.',
        ]);
    }

    public function delete(?string $path): void
    {
        if ($path !== null) {
            Storage::disk($this->disk)->delete($path);
        }
    }
}
