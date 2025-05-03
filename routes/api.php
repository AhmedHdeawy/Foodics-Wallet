<?php

use App\Http\Controllers\Webhook\WebhookController;
use Illuminate\Support\Facades\Route;

// Webhook routes
Route::post('/webhooks/{bank}', [WebhookController::class, 'receive'])
    ->middleware('throttle:webhook')
    ->name('webhooks.receive');
