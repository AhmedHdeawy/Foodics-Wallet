<?php

namespace App\Services\Webhooks\Concretes;

use App\Jobs\ProcessWebhook;
use App\Models\Webhook;
use App\Services\BankParsers\Concretes\BankParserFactory;
use App\Services\Transactions\Contracts\TransactionServiceContract;
use App\Services\Webhooks\Contracts\WebhookServiceContract;
use Exception;
use Illuminate\Support\Facades\Log;

class WebhookService implements WebhookServiceContract
{
    public function __construct(
        protected BankParserFactory $bankParserFactory,
        protected TransactionServiceContract $transactionService
    ) {}

    public function handleReceivedWebhook(array $data): Webhook
    {
        $webhook = Webhook::query()->create($data);

        ProcessWebhook::dispatchSync($webhook);

        return $webhook;
    }

    /**
     * @throws Exception
     */
    public function processWebhook(Webhook $webhook): void
    {
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
            Log::error("Error processing webhook $webhook->id: {$e->getMessage()}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
