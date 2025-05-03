<?php

namespace App\Services\Webhooks\Concretes;

use App\Jobs\ProcessWebhook;
use App\Models\Transaction;
use App\Models\Webhook;
use App\Services\BankParsers\Concretes\BankParserFactory;
use App\Services\Webhooks\Contracts\WebhookServiceContract;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookService implements WebhookServiceContract
{
    public function __construct(protected BankParserFactory $bankParserFactory)
    {
    }

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

            $this->processTransactionsInChunks($transactions, 100);

            $webhook->markAsProcessed();
        } catch (Exception $e) {
            Log::error("Error processing webhook {$webhook->id}: {$e->getMessage()}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Process transactions in chunks
     *
     * @param  array  $transactions
     * @param  int  $chunkSize
     * @return void
     */
    protected function processTransactionsInChunks(array $transactions, int $chunkSize): void
    {
        $chunks = array_chunk($transactions, $chunkSize);

        foreach ($chunks as $chunk) {
            $this->processTransactionChunk($chunk);
        }
    }

    /**
     * Process a chunk of transactions.
     *
     * @param  array  $transactions
     * @return void
     */
    private function processTransactionChunk(array $transactions): void
    {
        DB::beginTransaction();
        try {
            /**
             * This is the simplest way to insert transactions in bulk.
             * it uses the DB unique constraint to ensure that no duplicates are inserted.
             * See the migration file for the unique index.
             */
            Transaction::query()->insertOrIgnore($transactions);

            /**
             * the other way to do this and ensure uniqueness is to use the unique identifier.
             */
            // $this->processTransactionsUsingUniqueIdentifier($transactions);

            Log::info("Batch processed ".count($transactions)." new transactions");

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
    }

    private function processTransactionsUsingUniqueIdentifier(array $transactions): void
    {
        // Extract all unique identifiers
        $uniqueIdentifiers = array_column($transactions, 'unique_identifier');

        // Fetch existing transactions with these identifiers in a single query
        $existingIdentifiers = Transaction::query()
            ->whereIn('unique_identifier', $uniqueIdentifiers)
            ->select('unique_identifier')
            ->pluck('unique_identifier')
            ->toArray();

        $newTransactions = array_values(array_filter($transactions, function ($transaction) use ($existingIdentifiers) {
            if (in_array($transaction['unique_identifier'], $existingIdentifiers)) {
                // We could store the duplicate transactions in the database for further analysis
                Log::info("Skipping duplicate transaction: {$transaction['reference']}");
                return false;
            }

            return $transaction;
        }));

        if (!empty($newTransactions)) {
            Transaction::query()->insertOrIgnore($newTransactions);
        }
    }
}
