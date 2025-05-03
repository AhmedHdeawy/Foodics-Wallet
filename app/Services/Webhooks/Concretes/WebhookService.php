<?php

namespace App\Services\Webhooks\Concretes;

use App\Jobs\ProcessWebhook;
use App\Models\Webhook;
use App\Services\Webhooks\Contracts\WebhookServiceContract;
use Illuminate\Support\Facades\Log;

class WebhookService implements WebhookServiceContract
{
    public function handleReceivedWebhook(array $data): Webhook
    {
        $webhook = Webhook::query()->create($data);

        ProcessWebhook::dispatch($webhook);

        return $webhook;
    }

    public function processWebhook(Webhook $webhook): void
    {
        if ($webhook->doNotProcess()) {
            Log::info("Skipping webhook {$webhook->id} as it's already {$webhook->status->value}");

            return;
        }

        $webhook->markAsProcessing();

        // Process the webhook data here
        $webhook->markAsProcessed();
    }
}
