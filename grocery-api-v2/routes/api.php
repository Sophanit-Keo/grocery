<?php

use App\Http\Controllers\Api\V1\Auth\BrowserSessionController;
use App\Http\Controllers\Api\V1\Auth\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')
    ->name('api.v1.auth.')
    ->middleware(['auth:sanctum', 'throttle:api'])
    ->group(function (): void {
        Route::get('/me', UserController::class)->name('me');
        Route::get('/sessions', [BrowserSessionController::class, 'index'])->name('sessions.index');
        Route::delete('/sessions/other', [BrowserSessionController::class, 'destroyOther'])
            ->name('sessions.destroy_other');
    });
