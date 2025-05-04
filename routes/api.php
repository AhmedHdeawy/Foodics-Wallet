<?php

use App\Http\Controllers\Webhook\WebhookController;
use Illuminate\Support\Facades\Route;

// Webhook routes
Route::prefix('webhooks')
    ->name('webhooks.')
    ->middleware('throttle:webhook')
    ->group(function () {
        Route::post('/{bank}', [WebhookController::class, 'handle'])->name('handle');
        Route::get('/{id}/status', [WebhookController::class, 'status'])->name('status');
    });
