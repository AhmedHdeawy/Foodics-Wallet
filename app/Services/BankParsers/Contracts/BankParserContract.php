<?php

namespace App\Services\BankParsers\Contracts;

interface BankParserContract
{
    public function parseTransactions(string $webhookData, int $clientId): array;

    public function getBankName(): string;
}
