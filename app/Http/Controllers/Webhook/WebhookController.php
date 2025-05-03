<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class WebhookController extends Controller
{

    public function receive($bank): JsonResponse
    {
        // Handle the webhook request here

        return response()->json(['message' => 'Webhook received successfully']);
    }

}
