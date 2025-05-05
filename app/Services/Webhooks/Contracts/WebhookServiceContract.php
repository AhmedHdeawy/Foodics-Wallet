<?php

namespace App\Services\Webhooks\Contracts;

interface WebhookServiceContract
{
    public function handleReceivedWebhook(array $data): int;

    public function processWebhook(int $webhookId): void;

    public function processPendingWebhooks(int $limit): void;

    public function getWebhookStatus(int $id): string;
}
