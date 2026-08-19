<?php

use App\Services\SpotImageStorage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    Storage::fake('public');
});

test('it stores a decoded png data url', function () {
    $path = (new SpotImageStorage('public'))->storeDataUrl(
        'data:image/png;base64,'.base64_encode('image contents'),
    );

    expect($path)->toStartWith('spots/')
        ->and($path)->toEndWith('.png');
    Storage::disk('public')->assertExists($path);
});

test('it uses a jpg extension for jpeg data', function () {
    $path = (new SpotImageStorage('public'))->storeDataUrl(
        'data:image/jpeg;base64,'.base64_encode('image contents'),
    );

    expect($path)->toEndWith('.jpg');
});

test('it rejects invalid base64 data', function () {
    (new SpotImageStorage('public'))->storeDataUrl('data:image/png;base64,%%%');
})->throws(ValidationException::class);

test('it deletes an existing image', function () {
    Storage::disk('public')->put('spots/to-delete.jpg', 'image');

    (new SpotImageStorage('public'))->delete('spots/to-delete.jpg');

    Storage::disk('public')->assertMissing('spots/to-delete.jpg');
});
