<?php

use App\DTOs\TransactionData;
use App\Enums\Bank;
use App\Services\BankParsers\Concretes\AcmeBankParser;
use Carbon\Carbon;

beforeEach(function () {
    $this->parser = new AcmeBankParser();
    $this->validSingleTransaction = "156,50//202504159000001//20250415";
    $this->validMultipleTransactions = "156,50//202504159000001//20250415\n".
        "7623,88//2024110556873465//20241105";
});

it('parses valid webhook with one transaction', function () {
    $transactions = $this->parser->parseTransactions($this->validSingleTransaction);

    expect($transactions)->toHaveCount(1)
        ->and($transactions[0]['amount'])->toBe(156.50)
        ->and($transactions[0]['reference'])->toBe('202504159000001')
        ->and($transactions[0]['transaction_date'])->toBe('2025-04-15')
        ->and($transactions[0]['bank_name'])->toBe(Bank::ACME->value);
});

it('maps the line to transaction DTO', function () {
    $transaction = $this->parser->mapLineToTransaction($this->validSingleTransaction);

    expect($transaction)->toBeInstanceOf(TransactionData::class);

    // Use Reflection API to access private properties.
    $reflectionClass = new ReflectionClass(TransactionData::class);
    $amountProperty = $reflectionClass->getProperty('amount');
    expect($amountProperty->getValue($transaction))->toBe(156.50);
});

it('parses valid webhook with multiple transaction', function () {
    $transactions = $this->parser->parseTransactions($this->validMultipleTransactions);

    expect($transactions)->toHaveCount(2);

    $expectedData = [
        [
            'amount' => 156.50,
            'reference' => '202504159000001',
            'transaction_date' => '2025-04-15'
        ],
        [
            'amount' => 7623.88,
            'reference' => '2024110556873465',
            'transaction_date' => '2024-11-05'
        ]
    ];

    foreach ($transactions as $index => $transaction) {
        $expected = $expectedData[$index];
        expect($transaction['amount'])->toBe($expected['amount'])
            ->and($transaction['reference'])->toBe($expected['reference'])
            ->and($transaction['transaction_date'])->toBe($expected['transaction_date'])
            ->and($transaction['bank_name'])->toBe(Bank::ACME->value);
    }
});

it('skips invalid lines when parsing transactions', function () {
    $webhookData = $this->validSingleTransaction."\n".
        "invalid transaction\n".
        "6,34//20230412576342//20230412\n".
        "20251315156,50";

    $transactions = $this->parser->parseTransactions($webhookData);

    expect($transactions)->toHaveCount(2)
        ->and($transactions[0]['reference'])->toBe('202504159000001')
        ->and($transactions[1]['reference'])->toBe('20230412576342');
});

it('skips transactions with dates in the future', function () {
    Carbon::setTestNow('2025-05-02');
    $futureDate = now()->addDays(5)->format('Ymd');

    $webhookData = "156,50//{$futureDate}9000001//{$futureDate}\n".
        "6,34//20230412576342//20230412\n";

    $transactions = $this->parser->parseTransactions($webhookData);

    expect($transactions)->toHaveCount(1)
        ->and($transactions[0]['reference'])->toBe('20230412576342')
        ->and($transactions[0]['transaction_date'])->toBe('2023-04-12');

    $transaction = $transactions[0];
    expect($transaction['amount'])->toBe(6.34)
        ->and($transaction['reference'])->toBe('20230412576342')
        ->and($transaction['transaction_date'])->toBe('2023-04-12')
        ->and($transaction['bank_name'])->toBe(Bank::ACME->value);
});

it('skips transactions with negative amount', function () {
    $webhookData = "-156,50//202504159000001//20250415\n".
        "6,34//20230412576342//20230412\n";

    $transactions = $this->parser->parseTransactions($webhookData);

    expect($transactions)->toHaveCount(1);

    $transaction = $transactions[0];
    expect($transaction['amount'])->toBe(6.34)
        ->and($transaction['reference'])->toBe('20230412576342')
        ->and($transaction['transaction_date'])->toBe('2023-04-12')
        ->and($transaction['bank_name'])->toBe(Bank::ACME->value);
});

it('handles empty webhook', function () {
    $transactions = $this->parser->parseTransactions("");
    expect($transactions)->toBeArray()->toBeEmpty();

    $transactions = $this->parser->parseTransactions("   ");
    expect($transactions)->toBeArray()->toBeEmpty();
});

it('returns the correct bank name', function () {
    expect($this->parser->getBankName())->toBeString()
        ->toEqual(Bank::ACME->value);
});

it('handles malformed input', function () {
    $malformedInputs = [
        "156,50##202504159000001//20250415", // Wrong format
        "156,50//202504159000001", // Incomplete
        "//", // Just a delimiter
        "156,50", // Missing required parts
    ];

    foreach ($malformedInputs as $input) {
        $transactions = $this->parser->parseTransactions($input);
        expect($transactions)->toBeEmpty();
    }
});
