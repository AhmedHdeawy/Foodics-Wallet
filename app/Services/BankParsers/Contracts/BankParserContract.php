<?php

namespace App\Services\BankParsers\Contracts;

interface BankParserContract
{
    /**
     * Parse transactions from webhook data
     *
     * @param  string  $webhookData  Raw webhook data
     * @return array Array of parsed transactions
     */
    public function parseTransactions(string $webhookData): array;

    /**
     * Get the bank name handled by this parser
     *
     * @return string
     */
    public function getBankName(): string;
}
