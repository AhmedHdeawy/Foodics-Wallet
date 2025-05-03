<?php

namespace App\Http\Controllers\Webhook;

use App\Enums\Bank;
use App\Http\Controllers\Controller;
use App\Services\Webhooks\Contracts\WebhookServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{

    public function __construct(protected WebhookServiceContract $webhookService)
    {
    }

    public function receive(Request $request, Bank $bank): JsonResponse
    {
        $webhook = $this->webhookService->store($request->all());

        return response()->json([
            'webhook_id' => $webhook->id,
            'message' => 'Webhook received successfully'
        ]);
    }

}
