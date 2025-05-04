<?php

namespace App\Services\Webhooks\Contracts;

use App\Models\Webhook;

interface WebhookServiceContract
{
    public function handleReceivedWebhook(array $data): int;

    public function processWebhook(int $webhookId): void;

    public function processPendingWebhooks(int $limit): void;
}
