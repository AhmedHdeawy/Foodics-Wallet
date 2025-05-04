<?php

use App\DTOs\TransactionData;
use App\Enums\Bank;
use App\Services\BankParsers\Concretes\FoodicsBankParser;
use Carbon\Carbon;

beforeEach(function () {
    $this->parser = new FoodicsBankParser();
    $this->validSingleTransaction = "20250415156,50#202504159000001#note/debt payment march/internal_reference/A462JE81";
    $this->validMultipleTransactions = "20250415156,50#202504159000001#note/debt payment march/internal_reference/A462JE81\n"
        ."20250416200,00#202504169000002#note/salary payment";
});

it('parses valid webhook with one transaction', function () {
    $transactions = $this->parser->parseTransactions($this->validSingleTransaction);

    expect($transactions)->toHaveCount(1)
        ->and($transactions[0]['amount'])->toBe(156.50)
        ->and($transactions[0]['reference'])->toBe('202504159000001')
        ->and($transactions[0]['transaction_date'])->toBe('2025-04-15')
        ->and($transactions[0]['bank_name'])->toBe(Bank::FOODICS->value)
        ->and($transactions[0]['meta'])->toBeString();

    // Test meta field structure
    $meta = json_decode($transactions[0]['meta'], true);
    expect($meta)->toBeArray()
        ->toHaveCount(2)
        ->toHaveKeys(['note', 'internal_reference'])
        ->and($meta['note'])->toBe('debt payment march')
        ->and($meta['internal_reference'])->toBe('A462JE81');
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
            'transaction_date' => '2025-04-15',
            'meta_keys' => ['note', 'internal_reference'],
            'meta_values' => ['debt payment march', 'A462JE81']
        ],
        [
            'amount' => 200.00,
            'reference' => '202504169000002',
            'transaction_date' => '2025-04-16',
            'meta_keys' => ['note'],
            'meta_values' => ['salary payment']
        ]
    ];

    foreach ($transactions as $index => $transaction) {
        $expected = $expectedData[$index];
        expect($transaction['amount'])->toBe($expected['amount'])
            ->and($transaction['reference'])->toBe($expected['reference'])
            ->and($transaction['transaction_date'])->toBe($expected['transaction_date'])
            ->and($transaction['bank_name'])->toBe(Bank::FOODICS->value);

        $meta = json_decode($transaction['meta'], true);
        expect($meta)->toBeArray();

        foreach ($expected['meta_keys'] as $i => $key) {
            expect($meta)->toHaveKey($key)
                ->and($meta[$key])->toBe($expected['meta_values'][$i]);
        }
    }
});

it('skips invalid lines when parsing transactions', function () {
    $webhookData = $this->validSingleTransaction."\n".
        "invalid transaction\n".
        "20250416200,00#202504169000002#note/salary payment\n".
        "20251315156,50";

    $transactions = $this->parser->parseTransactions($webhookData);

    expect($transactions)->toHaveCount(2)
        ->and($transactions[0]['reference'])->toBe('202504159000001')
        ->and($transactions[1]['reference'])->toBe('202504169000002');
});

it('skips transactions with dates in the future', function () {
    Carbon::setTestNow('2025-05-02');
    $futureDate = now()->addDays(5)->format('Ymd');
    $webhookData = "{$futureDate}156,50#{$futureDate}9000001#note/debt payment march/internal_reference/A462JE81\n".
        "20250416200,00#202506169000002#note/salary payment\n";

    $transactions = $this->parser->parseTransactions($webhookData);

    expect($transactions)->toHaveCount(1)
        ->and($transactions[0]['reference'])->toBe('202506169000002')
        ->and($transactions[0]['transaction_date'])->toBe('2025-04-16');
});

it('skips transactions with negative amount', function () {
    $webhookData = "20250415-156,50#202504159000001#note/debt payment march/internal_reference/A462JE81\n".
        "20250416200,00#202506169000002#note/salary payment\n";

    $transactions = $this->parser->parseTransactions($webhookData);

    expect($transactions)->toHaveCount(1)
        ->and($transactions[0]['amount'])->toBe(200.00)
        ->and($transactions[0]['reference'])->toBe('202506169000002');
});

it('handles empty webhook', function () {
    $transactions = $this->parser->parseTransactions("");
    expect($transactions)->toBeArray()->toBeEmpty();

    $transactions = $this->parser->parseTransactions("   ");
    expect($transactions)->toBeArray()->toBeEmpty();
});

it('returns the correct bank name', function () {
    expect($this->parser->getBankName())->toBeString()
        ->toEqual(Bank::FOODICS->value);
});

it('handles malformed input', function () {
    $malformedInputs = [
        "20250145#100,00#202504159000001#note/test", // Wrong format
        "202504151#00,00#", // Incomplete
        "#", // Just a delimiter
        "20250415100,00", // Missing required parts
    ];

    foreach ($malformedInputs as $input) {
        $transactions = $this->parser->parseTransactions($input);
        expect($transactions)->toBeEmpty();
    }
});
