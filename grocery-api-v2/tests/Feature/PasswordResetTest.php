<?php

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

test('password reset requests do not reveal whether an account exists', function () {
    Notification::fake();
    $user = User::factory()->create();
    $message = 'If an account exists for that email address, a password reset link has been sent.';

    $this->postJson('/api/v1/auth/forgot-password', ['email' => $user->email])
        ->assertOk()
        ->assertJsonPath('message', $message);

    $this->postJson('/api/v1/auth/forgot-password', ['email' => 'missing@example.com'])
        ->assertOk()
        ->assertJsonPath('message', $message);

    Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user): bool {
        return str_starts_with(
            $notification->toMail($user)->actionUrl,
            config('app.frontend_url').'/reset-password?'
        );
    });
});

test('a valid reset token changes the password and revokes existing sessions', function () {
    config(['session.driver' => 'database']);

    $user = User::factory()->create();
    DB::table(config('session.table'))->insert([
        'id' => 'existing-session',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => '',
        'last_activity' => now()->timestamp,
    ]);

    $token = Password::createToken($user);

    $this->postJson('/api/v1/auth/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewFreshCart!2026',
        'password_confirmation' => 'NewFreshCart!2026',
    ])->assertOk();

    expect(Hash::check('NewFreshCart!2026', $user->fresh()->password))->toBeTrue()
        ->and(DB::table(config('session.table'))->where('user_id', $user->id)->count())->toBe(0);
});

test('an invalid reset token is rejected', function () {
    $user = User::factory()->create();

    $this->postJson('/api/v1/auth/reset-password', [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'NewFreshCart!2026',
        'password_confirmation' => 'NewFreshCart!2026',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');
});

test('an expired reset token is rejected', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $this->travelTo(now()->addMinutes(config('auth.passwords.users.expire') + 1));

    $this->postJson('/api/v1/auth/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewFreshCart!2026',
        'password_confirmation' => 'NewFreshCart!2026',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');
});

test('password reset requests are throttled', function () {
    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'rate-limited@example.com',
        ])->assertOk();
    }

    $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'rate-limited@example.com',
    ])->assertTooManyRequests();
});
