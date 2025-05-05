<?php

namespace App\Repositories\Transaction\Contracts;

use App\Repositories\Base\Contracts\BaseRepositoryContract;

interface TransactionRepositoryContract extends BaseRepositoryContract
{
    public function insertTransactionsChunk(array $transactions): void;

    public function sumClientBalance(int $clientId): float;
}
