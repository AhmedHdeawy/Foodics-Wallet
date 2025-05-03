<?php

namespace App\Services\Webhooks\Contracts;

use App\Models\Webhook;

interface WebhookServiceContract
{
    public function handleReceivedWebhook(array $data): Webhook;

    public function processWebhook(Webhook $webhook): void;
}
