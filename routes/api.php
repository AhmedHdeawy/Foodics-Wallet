<?php

use App\Http\Controllers\Transfer\TransferController;
use App\Http\Controllers\Webhook\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->group(function () {
        // Webhook routes
        Route::prefix('webhooks')
            ->name('webhooks.')
            ->middleware('throttle:webhook')
            ->group(function () {
                Route::post('/{bank}', [WebhookController::class, 'handle'])->name('handle');
                Route::get('/{id}/status', [WebhookController::class, 'status'])->name('status');
            });

        // Send money routes
        Route::post('transfer', [TransferController::class, 'transfer'])
            ->name('transfer.handle')
            ->middleware('throttle:transfer');
    });
