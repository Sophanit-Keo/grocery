<?php

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('a user can update their profile and an email change requires verification', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson('/api/v1/auth/profile', [
            'first_name' => 'Jamie',
            'last_name' => 'Taylor',
            'email' => 'NEW@EXAMPLE.COM',
        ])
        ->assertOk()
        ->assertJsonPath('data.user.email', 'new@example.com')
        ->assertJsonPath('data.user.is_verified', false);

    expect($user->fresh()->email_verified_at)->toBeNull();
    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

test('a user can change their password and other database sessions are revoked', function () {
    config(['session.driver' => 'database']);
    $user = User::factory()->create();

    DB::table(config('session.table'))->insert([
        'id' => 'other-session',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.2',
        'user_agent' => 'Other browser',
        'payload' => '',
        'last_activity' => now()->timestamp,
    ]);

    $this->actingAs($user)
        ->putJson('/api/v1/auth/password', [
            'current_password' => 'password',
            'password' => 'UpdatedFreshCart!2026',
            'password_confirmation' => 'UpdatedFreshCart!2026',
        ])
        ->assertSuccessful();

    expect(Hash::check('UpdatedFreshCart!2026', $user->fresh()->password))->toBeTrue()
        ->and(DB::table(config('session.table'))->where('id', 'other-session')->exists())->toBeFalse();
});

test('users can inspect their own sessions without receiving session identifiers', function () {
    config(['session.driver' => 'database']);
    $this->withHeader('Origin', config('app.frontend_url'));
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    foreach ([[$user->id, 'Own browser'], [$otherUser->id, 'Other browser']] as [$userId, $agent]) {
        DB::table(config('session.table'))->insert([
            'id' => fake()->uuid(),
            'user_id' => $userId,
            'ip_address' => '127.0.0.1',
            'user_agent' => $agent,
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);
    }

    $this->actingAs($user)
        ->getJson('/api/v1/auth/sessions')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.user_agent', 'Own browser')
        ->assertJsonMissingPath('data.0.id');
});

test('logging out other sessions requires the current password', function () {
    config(['session.driver' => 'database']);
    $this->withHeader('Origin', config('app.frontend_url'));
    $user = User::factory()->create();

    DB::table(config('session.table'))->insert([
        'id' => 'other-session',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.2',
        'user_agent' => 'Other browser',
        'payload' => '',
        'last_activity' => now()->timestamp,
    ]);

    $this->actingAs($user)
        ->deleteJson('/api/v1/auth/sessions/other', ['password' => 'wrong-password'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');

    expect(DB::table(config('session.table'))->where('id', 'other-session')->exists())->toBeTrue();

    $this->deleteJson('/api/v1/auth/sessions/other', ['password' => 'password'])
        ->assertNoContent();

    expect(DB::table(config('session.table'))->where('id', 'other-session')->exists())->toBeFalse();
});
