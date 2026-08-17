<?php

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('a user can register with normalized profile information', function () {
    Notification::fake();

    $response = $this->postJson('/api/v1/auth/register', [
        'first_name' => '  Alex  ',
        'last_name' => '  Morgan  ',
        'email' => 'ALEX@EXAMPLE.COM',
        'password' => 'FreshCart!2026',
        'password_confirmation' => 'FreshCart!2026',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.user.email', 'alex@example.com')
        ->assertJsonPath('data.user.is_verified', false)
        ->assertJsonMissingPath('data.user.password');

    $user = User::query()->sole();

    expect($user->first_name)->toBe('Alex')
        ->and($user->last_name)->toBe('Morgan')
        ->and(Hash::check('FreshCart!2026', $user->password))->toBeTrue();

    $this->assertAuthenticatedAs($user);
    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

test('registration enforces the strong password policy', function () {
    $this->postJson('/api/v1/auth/register', [
        'first_name' => 'Alex',
        'last_name' => 'Morgan',
        'email' => 'alex@example.com',
        'password' => 'weak-password',
        'password_confirmation' => 'weak-password',
    ])->assertUnprocessable()->assertJsonValidationErrors('password');
});

test('a user can log in fetch their account and log out', function () {
    $user = User::factory()->create();

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()
        ->assertJsonPath('two_factor', false)
        ->assertJsonPath('data.user.id', $user->id);

    $this->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.email', $user->email);

    $this->postJson('/api/v1/auth/logout')->assertNoContent();
    $this->assertGuest();
    $this->app['auth']->forgetGuards();
    $this->getJson('/api/v1/auth/me')->assertUnauthorized();
});

test('invalid credentials use validation errors and login attempts are throttled', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'incorrect-password',
    ])->assertTooManyRequests();
});
