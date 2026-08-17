<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

test('trusted proxy headers preserve the public https origin', function () {
    config(['trustedproxy.proxies' => '*']);

    Route::get('/_deployment/proxy-origin', fn (Request $request): array => [
        'origin' => $request->getSchemeAndHttpHost(),
    ]);

    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.10'])
        ->withHeaders([
            'X-Forwarded-Host' => 'api.example.com',
            'X-Forwarded-Proto' => 'https',
        ])
        ->getJson('/_deployment/proxy-origin')
        ->assertOk()
        ->assertJsonPath('origin', 'https://api.example.com');
});
