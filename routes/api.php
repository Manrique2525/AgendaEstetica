<?php

use App\Http\Controllers\Api\V1\AdminAuthController;
use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class)->name('api.health');

Route::prefix('admin/auth')->group(function (): void {
    Route::post('/login', [AdminAuthController::class, 'login'])
        ->middleware('throttle:admin-login')
        ->name('admin.auth.login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', [AdminAuthController::class, 'me'])->name('admin.auth.me');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.auth.logout');
    });
});
