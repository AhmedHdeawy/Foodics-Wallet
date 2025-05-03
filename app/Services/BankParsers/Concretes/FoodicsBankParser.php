<?php

namespace App\Services\BankParsers\Concretes;

use App\Enums\Bank;
use App\Services\BankParsers\Contracts\BankParserContract;

class FoodicsBankParser implements BankParserContract
{

    /**
     * Format: Date, Amount (two decimals), "#", Reference, "#", Key-value pairs
     * Example: 20250615156,50#202506159000001#note/debt payment march/internal_reference/A462JE81
     *
     * @param  string  $webhookData  Raw webhook data
     * @return array Array of parsed transactions
     */
    public function parseTransactions(string $webhookData): array
    {
        // TODO: Implement parseTransactions() method.
    }
}
