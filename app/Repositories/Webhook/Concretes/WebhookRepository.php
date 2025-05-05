<?php

namespace App\Repositories\Webhook\Concretes;

use App\Enums\WebhookStatus;
use App\Models\Webhook;
use App\Repositories\Base\Concretes\BaseRepository;
use App\Repositories\Webhook\Contracts\WebhookRepositoryContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class WebhookRepository extends BaseRepository implements WebhookRepositoryContract
{
    /**
     * Specify Model class name
     */
    protected function model(): string
    {
        return Webhook::class;
    }

    public function storeNewWebhookAndGetId(array $data): int
    {
        return DB::table($this->model->getModel()->getTable())->insertGetId($data);
    }

    public function getPendingWebhooks(int $limit = 1000, array $columns = ['*']): Collection
    {
        return $this->model
            ->where('status', WebhookStatus::PENDING)
            ->limit($limit)
            ->get($columns);
    }

    public function markAsProcessing(Webhook $webhook): void
    {
        $webhook->update([
            'status' => WebhookStatus::PROCESSING,
        ]);
    }

    public function markAsProcessed(Webhook $webhook): void
    {
        $webhook->update([
            'status' => WebhookStatus::PROCESSED,
        ]);
    }

    public function markAsFailed(Webhook $webhook, ?string $errorMessage = null): void
    {
        $webhook->update([
            'status' => WebhookStatus::FAILED,
            'error_message' => $errorMessage,
        ]);
    }
}
