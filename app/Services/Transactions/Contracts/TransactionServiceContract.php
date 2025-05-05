<?php

namespace App\Services\Transactions\Contracts;

interface TransactionServiceContract
{
    public function processTransactions(array $transactions): void;

    public function sumClientBalance(int $clientId): float;
}
