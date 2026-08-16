<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

test('r2 can store and read an object', function () {
    if (! config('filesystems.disks.r2.key') || ! config('filesystems.disks.r2.secret')) {
        $this->markTestSkipped('R2 credentials are not configured.');
    }

    $disk = Storage::disk('r2');
    $path = 'tests/'.Str::uuid().'.png';
    $image = file_get_contents(public_path('apple-touch-icon.png'));

    expect($image)->not->toBeFalse();

    try {
        expect($disk->put($path, $image))->toBeTrue()
            ->and($disk->exists($path))->toBeTrue()
            ->and(hash('sha256', $disk->get($path)))->toBe(hash('sha256', $image));
    } finally {
        $disk->delete($path);
    }

    expect($disk->exists($path))->toBeFalse();
})->group('r2');
