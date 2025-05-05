<?php

namespace App\Services\Transactions\Concretes;

use App\Repositories\Transaction\Contracts\TransactionRepositoryContract;
use App\Services\Transactions\Contracts\TransactionServiceContract;

class TransactionService implements TransactionServiceContract
{
    public function __construct(protected TransactionRepositoryContract $transactionRepository)
    {
    }

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
        /**
         * This is the simplest way to insert transactions in bulk.
         * it uses the DB unique constraint to ensure that no duplicates are inserted.
         * See the migration file for the unique index.
         */
        $this->transactionRepository->insertTransactionsChunk($transactions);

        /**
         * the other way to do this and ensure uniqueness is to use the unique identifier.
         * Please refer to the "Duplicate Transaction Prevention" section in the README file.
         */
        // $this->processTransactionsUsingUniqueIdentifier($transactions);
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
    //             logger()->info("Skipping duplicate transaction: {$transaction['reference']}");
    //             return false;
    //         }
    //
    //         return $transaction;
    //     }));
    //
    //     if (!empty($newTransactions)) {
    //         $this->transactionRepository->insertTransactionsChunk($newTransactions);
    //     }
    // }

    public function sumClientBalance(int $clientId): float
    {
        return $this->transactionRepository->sumClientBalance($clientId);
    }
}
