<?php

namespace App\Services\BankParsers\Contracts;

interface BankParserContract
{
    public function parseTransactions(string $webhookData): array;

    public function getBankName(): string;
}
