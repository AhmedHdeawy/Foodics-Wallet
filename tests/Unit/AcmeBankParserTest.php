<?php

use App\DTOs\TransactionData;
use App\Enums\Bank;
use App\Services\BankParsers\Concretes\AcmeBankParser;

it('parses valid webhook with one transaction', function () {
    $parser = new AcmeBankParser();

    $webhookData = "156,50//202504159000001//20250415";

    $transactions = $parser->parseTransactions($webhookData);

    expect($transactions)->toHaveCount(1)
        ->and($transactions[0]['amount'])->toBe(156.50)
        ->and($transactions[0]['reference'])->toBe('202504159000001')
        ->and($transactions[0]['transaction_date'])->toBe('2025-04-15')
        ->and($transactions[0]['bank_name'])->toBe(Bank::ACME->value);
});

it('maps the line to transaction DTO', function () {
    $parser = new AcmeBankParser();

    $webhookData = "156,50//202504159000001//20250415";

    $transaction = $parser->mapLineToTransaction($webhookData);

    expect($transaction)
        ->toBeInstanceOf(TransactionData::class);
});

it('parses valid webhook with multiple transaction', function () {
    $parser = new AcmeBankParser();

    $webhookData = "156,50//202504159000001//20250415\n".
        "7623,88//2024110556873465//20241105";

    $transactions = $parser->parseTransactions($webhookData);

    expect($transactions)->toHaveCount(2);

    $firstTransaction = $transactions[0];
    expect($firstTransaction['amount'])->toBe(156.50)
        ->and($firstTransaction['reference'])->toBe('202504159000001')
        ->and($firstTransaction['transaction_date'])->toBe('2025-04-15')
        ->and($firstTransaction['bank_name'])->toBe(Bank::ACME->value);

    $secondTransaction = $transactions[1];
    expect($secondTransaction['amount'])->toBe(7623.88)
        ->and($secondTransaction['reference'])->toBe('2024110556873465')
        ->and($secondTransaction['transaction_date'])->toBe('2024-11-05')
        ->and($secondTransaction['bank_name'])->toBe(Bank::ACME->value);
});

it('skips the invalid lines', function () {
    $parser = new AcmeBankParser();

    $webhookData = "156,50//202504159000001//20250415\n".
        "invalid transaction\n".
        "6,34//20230412576342//20230412\n".
        "20251315156,50";

    $transactions = $parser->parseTransactions($webhookData);

    expect($transactions)->toHaveCount(2);

    $firstTransaction = $transactions[0];
    expect($firstTransaction['amount'])->toBe(156.50)
        ->and($firstTransaction['reference'])->toBe('202504159000001')
        ->and($firstTransaction['transaction_date'])->toBe('2025-04-15')
        ->and($firstTransaction['bank_name'])->toBe(Bank::ACME->value);

    $secondTransaction = $transactions[1];
    expect($secondTransaction['amount'])->toBe(6.34)
        ->and($secondTransaction['reference'])->toBe('20230412576342')
        ->and($secondTransaction['transaction_date'])->toBe('2023-04-12')
        ->and($secondTransaction['bank_name'])->toBe(Bank::ACME->value);
});

it('skips transactions with dates in the future', function () {
    $parser = new AcmeBankParser();
    $futureDate = now()->addDays(5)->format('Ymd');

    $webhookData = "156,50//{$futureDate}9000001//{$futureDate}\n".
        "6,34//20230412576342//20230412\n";

    $transactions = $parser->parseTransactions($webhookData);

    expect($transactions)->toHaveCount(1);

    $transaction = $transactions[0];
    expect($transaction['amount'])->toBe(6.34)
        ->and($transaction['reference'])->toBe('20230412576342')
        ->and($transaction['transaction_date'])->toBe('2023-04-12')
        ->and($transaction['bank_name'])->toBe(Bank::ACME->value);
});

it('skips transactions with negative amount', function () {
    $parser = new AcmeBankParser();

    $webhookData = "-156,50//202504159000001//20250415\n".
        "6,34//20230412576342//20230412\n";

    $transactions = $parser->parseTransactions($webhookData);

    expect($transactions)->toHaveCount(1);

    $transaction = $transactions[0];
    expect($transaction['amount'])->toBe(6.34)
        ->and($transaction['reference'])->toBe('20230412576342')
        ->and($transaction['transaction_date'])->toBe('2023-04-12')
        ->and($transaction['bank_name'])->toBe(Bank::ACME->value);
});

it('handles empty webhook', function () {
    $parser = new AcmeBankParser();
    $webhookData = "";
    $transactions = $parser->parseTransactions($webhookData);

    expect($transactions)->toBeEmpty();
});

it('returns the correct bank name', function () {
    $parser = new AcmeBankParser();
    expect($parser->getBankName())->toBeString()
        ->toEqual(Bank::ACME->value);
});
