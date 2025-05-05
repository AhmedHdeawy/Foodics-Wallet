<?php

namespace App\Repositories\Webhook\Contracts;

use App\Models\Webhook;
use App\Repositories\Base\Contracts\BaseRepositoryContract;
use Illuminate\Database\Eloquent\Collection;

interface WebhookRepositoryContract extends BaseRepositoryContract
{
    public function storeNewWebhookAndGetId(array $data): int;

    public function getPendingWebhooks(int $limit = 1000, array $columns = ['*']): Collection;

    public function markAsProcessing(Webhook $webhook): void;

    public function markAsProcessed(Webhook $webhook): void;

    public function markAsFailed(Webhook $webhook, ?string $errorMessage = null): void;

}
