<?php

namespace App\Repositories\Transaction\Concretes;

use App\Models\Transaction;
use App\Repositories\Base\Concretes\BaseRepository;
use App\Repositories\Transaction\Contracts\TransactionRepositoryContract;
use Illuminate\Support\Facades\DB;

class TransactionRepository extends BaseRepository implements TransactionRepositoryContract
{
    /**
     * Specify Model class name
     */
    protected function model(): string
    {
        return Transaction::class;
    }

    public function insertTransactionsChunk(array $transactions): void
    {
        DB::transaction(function () use ($transactions) {
            /**
             * This is the simplest way to insert transactions in bulk.
             * it uses the DB unique constraint to ensure that no duplicates are inserted.
             * See the migration file for the unique index.
             */
            $this->model->insertOrIgnore($transactions);
        });
    }

    public function sumClientBalance(int $clientId): float
    {
        return $this->model
            ->where('client_id', $clientId)
            ->sum('amount');
    }
}
