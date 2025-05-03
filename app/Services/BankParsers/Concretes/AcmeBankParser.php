<?php

namespace App\Services\BankParsers\Concretes;

use App\DTOs\TransactionData;
use App\Enums\Bank;
use App\Services\BankParsers\Contracts\BankParserContract;
use App\Services\BankParsers\Contracts\MapLineToTransactionContract;

class AcmeBankParser implements BankParserContract, MapLineToTransactionContract
{
    /**
     * Format: Amount (two decimals), "//", Reference, "//", Date
     * Example: 156,50//202506159000001//20250615
     *
     * @param  string  $webhookData  Raw webhook data
     * @return TransactionData[] Array of parsed transactions
     */
    public function parseTransactions(string $webhookData): array
    {
        // TODO: Implement parseTransactions() method.
        return [];
    }

    public function mapLineToTransaction(string $line): TransactionData
    {
       // TODO: Implement mapLineToTransaction() method.
        return new TransactionData(
            reference: '',
            amount: 0.0,
            date: now(),
            meta: [],
            bank: $this->getBankName()
        );
    }

    public function getBankName(): string
    {
        return Bank::ACME->value;
    }
}
