<?php

namespace App\Http\Controllers\Webhook;

use App\Enums\Bank;
use App\Http\Controllers\Controller;
use App\Http\Requests\WebhookRequest;
use App\Services\Webhooks\Contracts\WebhookServiceContract;
use Illuminate\Http\JsonResponse;

class WebhookController extends Controller
{

    public function __construct(protected WebhookServiceContract $webhookService)
    {
    }

    public function receive(WebhookRequest $request, Bank $bank): JsonResponse
    {
        $webhook = $this->webhookService->store($request->validated());

        return response()->json([
            'webhook_id' => $webhook->id,
            'message' => 'Webhook received successfully'
        ]);
    }

}
