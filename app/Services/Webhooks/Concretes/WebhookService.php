<?php

namespace App\Services\Webhooks\Concretes;

use App\Jobs\ProcessWebhook;
use App\Models\Webhook;
use App\Repositories\Webhook\Contracts\WebhookRepositoryContract;
use App\Services\BankParsers\Concretes\BankParserFactory;
use App\Services\Clients\Contracts\ClientServiceContract;
use App\Services\Transactions\Contracts\TransactionServiceContract;
use App\Services\Webhooks\Contracts\WebhookServiceContract;
use Exception;
use Illuminate\Support\Facades\Log;

class WebhookService implements WebhookServiceContract
{
    public function __construct(
        protected BankParserFactory $bankParserFactory,
        protected TransactionServiceContract $transactionService,
        protected ClientServiceContract $clientService,
        protected WebhookRepositoryContract $webhookRepository
    ) {
    }

    public function handleReceivedWebhook(array $data): int
    {
        /*
         * For alternative webhook processing strategies and implementation options,
         * Please refer to the "Scalability and Webhook Processing Options" section in the README file.
         */
        $this->clientService->validateClient($data['client_id']);

        $webhookId = $this->webhookRepository->storeNewWebhookAndGetId($this->prepareWebhookData($data));

        ProcessWebhook::dispatch($webhookId);

        return $webhookId;
    }

    /**
     * @throws Exception
     */
    public function processWebhook(int $webhookId): void
    {
        /** @var Webhook $webhook */
        $webhook = $this->webhookRepository->findOrFail($webhookId, ['id', 'client_id', 'raw_data', 'bank_name']);

        if ($webhook->doNotProcess()) {
            return;
        }

        try {
            $this->webhookRepository->markAsProcessing($webhook);

            $parser = $this->bankParserFactory->getParser($webhook->bank_name);
            $transactions = $parser->parseTransactions($webhook->raw_data, $webhook->client_id);

            $this->transactionService->processTransactions($transactions);

            $this->webhookRepository->markAsProcessed($webhook);

            $this->clientService->updateBalance($webhook->client_id);
        } catch (Exception $e) {
            $this->webhookRepository->markAsFailed($webhook, $e->getMessage());
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
        $webhooks = $this->webhookRepository->getPendingWebhooks($limit, ['id']);

        foreach ($webhooks as $webhook) {
            /** @var Webhook $webhook */
            ProcessWebhook::dispatch($webhook->id);
        }
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
        /** @var Webhook $webhook */
        $webhook = $this->webhookRepository->findOrFail($id, ['status']);

        return $webhook->status->value;
    }
}
