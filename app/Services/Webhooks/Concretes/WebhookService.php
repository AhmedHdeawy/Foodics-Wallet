<?php

namespace App\Services\Webhooks\Concretes;

use App\Jobs\ProcessWebhook;
use App\Models\Webhook;
use App\Services\Webhooks\Contracts\WebhookServiceContract;

class WebhookService implements WebhookServiceContract
{

    /**
     * @param  array  $data
     * @return Webhook
     */
    public function handleReceivedWebhook(array $data): Webhook
    {
        $webhook = Webhook::query()->create($data);

        ProcessWebhook::dispatch($webhook);

        return $webhook;
    }

    public function processWebhook(Webhook $webhook): void
    {
        $webhook->markAsProcessing();

        // Process the webhook data here
        $webhook->markAsProcessed();
    }
}
