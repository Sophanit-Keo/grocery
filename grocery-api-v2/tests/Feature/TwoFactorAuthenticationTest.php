<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Fortify;

test('a user can enable confirm inspect and disable two factor authentication', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/v1/auth/confirmed-password-status')
        ->assertOk()
        ->assertJsonPath('confirmed', false);

    $this->actingAs($user)
        ->postJson('/api/v1/auth/confirm-password', ['password' => 'password'])
        ->assertSuccessful();

    $this->getJson('/api/v1/auth/confirmed-password-status')
        ->assertOk()
        ->assertJsonPath('confirmed', true);

    $this->postJson('/api/v1/auth/two-factor-authentication')->assertSuccessful();
    expect($user->fresh()->two_factor_secret)->not->toBeNull();

    $this->getJson('/api/v1/auth/two-factor-authentication/qr-code')
        ->assertOk()
        ->assertJsonStructure(['svg', 'url']);

    $provider = Mockery::mock(TwoFactorAuthenticationProvider::class);
    $provider->shouldReceive('verify')->once()->andReturnTrue();
    $this->app->instance(TwoFactorAuthenticationProvider::class, $provider);

    $this->postJson('/api/v1/auth/two-factor-authentication/confirm', ['code' => '123456'])
        ->assertSuccessful();

    expect($user->fresh()->hasEnabledTwoFactorAuthentication())->toBeTrue();

    $this->getJson('/api/v1/auth/two-factor-authentication/recovery-codes')
        ->assertOk()
        ->assertJsonCount(8);

    $oldCodes = $user->fresh()->recoveryCodes();

    $this->postJson('/api/v1/auth/two-factor-authentication/recovery-codes')
        ->assertSuccessful();

    expect($user->fresh()->recoveryCodes())->toHaveCount(8)->not->toBe($oldCodes);

    $this->deleteJson('/api/v1/auth/two-factor-authentication')->assertSuccessful();
    expect($user->fresh()->two_factor_secret)->toBeNull();
});

test('a configured user completes a two factor login challenge', function () {
    $user = User::factory()->create([
        'two_factor_secret' => Fortify::currentEncrypter()->encrypt('test-secret'),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode(['recovery-code'])),
        'two_factor_confirmed_at' => now(),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()->assertJsonPath('two_factor', true);

    $this->assertGuest();

    $provider = Mockery::mock(TwoFactorAuthenticationProvider::class);
    $provider->shouldReceive('verify')->once()->with('test-secret', '123456')->andReturnTrue();
    $this->app->instance(TwoFactorAuthenticationProvider::class, $provider);

    $this->postJson('/api/v1/auth/two-factor-challenge', ['code' => '123456'])
        ->assertNoContent();

    $this->assertAuthenticatedAs($user);
});

test('a recovery code can be used once to complete a challenge', function () {
    $user = User::factory()->create([
        'two_factor_secret' => Fortify::currentEncrypter()->encrypt('test-secret'),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode(['recovery-code'])),
        'two_factor_confirmed_at' => now(),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()->assertJsonPath('two_factor', true);

    $this->postJson('/api/v1/auth/two-factor-challenge', ['recovery_code' => 'recovery-code'])
        ->assertNoContent();

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->recoveryCodes())->not->toContain('recovery-code');
});

test('invalid two factor challenges are throttled and secrets stay hidden', function () {
    $user = User::factory()->create([
        'two_factor_secret' => Fortify::currentEncrypter()->encrypt('test-secret'),
        'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode(['recovery-code'])),
        'two_factor_confirmed_at' => now(),
    ]);

    $this->actingAs($user)
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonMissingPath('data.password')
        ->assertJsonMissingPath('data.two_factor_secret')
        ->assertJsonMissingPath('data.two_factor_recovery_codes');

    Auth::guard('web')->logout();
    $this->app['auth']->forgetGuards();

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()->assertJsonPath('two_factor', true);

    $provider = Mockery::mock(TwoFactorAuthenticationProvider::class);
    $provider->shouldReceive('verify')->times(5)->andReturnFalse();
    $this->app->instance(TwoFactorAuthenticationProvider::class, $provider);

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/v1/auth/two-factor-challenge', ['code' => '000000'])
            ->assertUnprocessable();
    }

    $this->postJson('/api/v1/auth/two-factor-challenge', ['code' => '000000'])
        ->assertTooManyRequests();
});
