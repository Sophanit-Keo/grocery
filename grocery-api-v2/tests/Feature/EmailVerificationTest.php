<?php

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

test('an authenticated user can verify their email with a signed link', function () {
    $user = User::factory()->unverified()->create();
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addHour(),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $this->actingAs($user)
        ->getJson($verificationUrl)
        ->assertNoContent();

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('invalid and expired verification signatures are rejected', function () {
    $user = User::factory()->unverified()->create();
    $expiredUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->subMinute(),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $this->actingAs($user)->getJson($expiredUrl)->assertForbidden();
    $this->getJson($expiredUrl.'&signature=invalid')->assertForbidden();
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('a verification email can be resent and links through the Vue application', function () {
    Notification::fake();
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->postJson('/api/v1/auth/email/verification-notification')
        ->assertAccepted();

    Notification::assertSentTo($user, VerifyEmailNotification::class, function (VerifyEmailNotification $notification) use ($user): bool {
        $actionUrl = $notification->toMail($user)->actionUrl;
        parse_str((string) parse_url($actionUrl, PHP_URL_QUERY), $query);

        return str_starts_with($actionUrl, config('app.frontend_url').'/verify-email?')
            && isset($query['verification_url'])
            && URL::hasValidSignature(request()->create($query['verification_url']));
    });
});
