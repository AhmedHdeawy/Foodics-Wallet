<?php

namespace App\Http\Controllers\Webhook;

use App\Enums\Bank;
use App\Http\Controllers\Controller;
use App\Http\Requests\WebhookRequest;
use App\Services\Webhooks\Contracts\WebhookServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(protected WebhookServiceContract $webhookService) {}

    public function handle(WebhookRequest $request, Bank $bank): JsonResponse
    {
        $webhookId = $this->webhookService->handleReceivedWebhook($request->validated());

        return $this->successResponse([
            'webhook_id' => $webhookId,
            'message' => 'Webhook received successfully',
        ]);
    }

    public function status(Request $request, int $id): JsonResponse
    {
        return $this->successResponse([
            'status' => $this->webhookService->getWebhookStatus($id),
        ]);
    }
}
