<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::emailVerification());
    Event::fake();
});

function verificationUrl(User $user, ?string $email = null, ?int $id = null): string
{
    return URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $id ?? $user->id, 'hash' => sha1($email ?? $user->email)],
    );
}

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get(route('verification.notice'))->assertOk();
});

test('email can be verified', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(verificationUrl($user));

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get(verificationUrl($user, 'wrong-email'));

    Event::assertNotDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('email is not verified with invalid user id', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get(verificationUrl($user, id: 123));

    Event::assertNotDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verified user is redirected to dashboard from verification prompt', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('verification.notice'))
        ->assertRedirect(route('dashboard', absolute: false));

    Event::assertNotDispatched(Verified::class);
});

test('already verified user visiting verification link is redirected without firing event', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(verificationUrl($user))
        ->assertRedirect(route('dashboard', absolute: false).'?verified=1');

    Event::assertNotDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});
