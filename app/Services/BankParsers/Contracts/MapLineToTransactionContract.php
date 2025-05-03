<?php

namespace App\Services\BankParsers\Contracts;

use App\DTOs\TransactionData;

interface MapLineToTransactionContract
{
    public function mapLineToTransaction(string $line): TransactionData;
}
