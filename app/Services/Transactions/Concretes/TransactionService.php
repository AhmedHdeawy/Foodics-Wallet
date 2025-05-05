<?php

namespace App\Services\Transactions\Concretes;

use App\Models\Transaction;
use App\Services\Transactions\Contracts\TransactionServiceContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService implements TransactionServiceContract
{
    /**
     * Process transactions in chunks
     */
    public function processTransactions(array $transactions): void
    {
        $chunks = array_chunk($transactions, 100);

        foreach ($chunks as $chunk) {
            $this->processTransactionChunk($chunk);
        }
    }

    /**
     * Process a chunk of transactions.
     */
    private function processTransactionChunk(array $transactions): void
    {
        DB::transaction(function () use ($transactions) {
            /**
             * This is the simplest way to insert transactions in bulk.
             * it uses the DB unique constraint to ensure that no duplicates are inserted.
             * See the migration file for the unique index.
             */
            Transaction::query()->insertOrIgnore($transactions);

            /**
             * the other way to do this and ensure uniqueness is to use the unique identifier.
             * Please refer to the "Duplicate Transaction Prevention" section in the README file.
             */
            // $this->processTransactionsUsingUniqueIdentifier($transactions);

            Log::info('Batch processed '.count($transactions).' new transactions');
        });
    }

    // private function processTransactionsUsingUniqueIdentifier(array $transactions): void
    // {
    //     // Extract all unique identifiers
    //     $uniqueIdentifiers = array_column($transactions, 'unique_identifier');
    //
    //     // Fetch existing transactions with these identifiers in a single query
    //     $existingIdentifiers = Transaction::query()
    //         ->whereIn('unique_identifier', $uniqueIdentifiers)
    //         ->select('unique_identifier')
    //         ->pluck('unique_identifier')
    //         ->toArray();
    //
    //     $newTransactions = array_values(array_filter($transactions, function ($transaction) use ($existingIdentifiers) {
    //         if (in_array($transaction['unique_identifier'], $existingIdentifiers)) {
    //             // We could store the duplicate transactions in the database for further analysis
    //             Log::info("Skipping duplicate transaction: {$transaction['reference']}");
    //             return false;
    //         }
    //
    //         return $transaction;
    //     }));
    //
    //     if (!empty($newTransactions)) {
    //         Transaction::query()->insertOrIgnore($newTransactions);
    //     }
    // }

    public function sumClientBalance(int $clientId): float
    {
        return Transaction::query()
            ->where('client_id', $clientId)
            ->sum('amount');
    }
}
