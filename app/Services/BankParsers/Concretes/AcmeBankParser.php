<?php

namespace App\Services\BankParsers\Concretes;

use App\Enums\Bank;
use App\Services\BankParsers\Contracts\BankParserContract;

class AcmeBankParser implements BankParserContract
{

    /**
     * Format: Amount (two decimals), "//", Reference, "//", Date
     * Example: 156,50//202506159000001//20250615
     *
     * @param  string  $webhookData  Raw webhook data
     * @return array Array of parsed transactions
     */
    public function parseTransactions(string $webhookData): array
    {
        // TODO: Implement parseTransactions() method.
    }
}
