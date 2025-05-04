<?php

namespace App\Services\Webhooks\Concretes;

use App\Jobs\ProcessWebhook;
use App\Models\Webhook;
use App\Services\BankParsers\Concretes\BankParserFactory;
use App\Services\Clients\Contracts\ClientServiceContract;
use App\Services\Transactions\Contracts\TransactionServiceContract;
use App\Services\Webhooks\Contracts\WebhookServiceContract;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookService implements WebhookServiceContract
{
    public function __construct(
        protected BankParserFactory $bankParserFactory,
        protected TransactionServiceContract $transactionService,
        protected ClientServiceContract $clientService
    ) {
    }

    public function handleReceivedWebhook(array $data): int
    {
        $this->clientService->validateClient($data['client_id']);

        $webhookId = DB::table('webhooks')->insertGetId($this->prepareWebhookData($data));

        ProcessWebhook::dispatch($webhookId);

        return $webhookId;
    }

    /**
     * @throws Exception
     */
    public function processWebhook(int $webhookId): void
    {
        $webhook = Webhook::query()->findOrFail($webhookId, ['id', 'client_id', 'raw_data', 'bank_name']);

        if ($webhook->doNotProcess()) {
            return;
        }

        try {
            $webhook->markAsProcessing();

            $parser = $this->bankParserFactory->getParser($webhook->bank_name);
            $transactions = $parser->parseTransactions($webhook->raw_data);

            $this->transactionService->processTransactions($transactions);

            $webhook->markAsProcessed();
        } catch (Exception $e) {
            $webhook->markAsFailed();
            Log::error("Error processing webhook $webhook->id: {$e->getMessage()}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function processPendingWebhooks(int $limit): void
    {
        $webhooks = $this->getPendingWebhooks($limit);

        foreach ($webhooks as $webhook) {
            /** @var Webhook $webhook */
            ProcessWebhook::dispatch($webhook->id);
        }
    }

    /**
     * Get pending webhooks
     */
    public function getPendingWebhooks(int $limit = 1000): Collection
    {
        return Webhook::pending()
            ->limit($limit)
            ->get(['id']);
    }

    private function prepareWebhookData(array $data): array
    {
        return [
            'client_id' => $data['client_id'],
            'raw_data' => $data['raw_data'],
            'bank_name' => $data['bank_name'],
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ];
    }

    public function getWebhookStatus(int $id): string
    {
        $webhook = Webhook::query()->findOrFail($id, ['status']);

        return $webhook->status->value;
    }

}
