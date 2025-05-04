<?php

use App\DTOs\TransactionData;
use App\Enums\Bank;
use App\Services\BankParsers\Concretes\FoodicsBankParser;

it('parses valid webhook with one transaction', function () {
    $parser = new FoodicsBankParser();

    $webhookData = "20250415156,50#202504159000001#note/debt payment march/internal_reference/A462JE81";

    $transactions = $parser->parseTransactions($webhookData);

    expect($transactions)->toHaveCount(1)
        ->and($transactions[0]['amount'])->toBe(156.50)
        ->and($transactions[0]['reference'])->toBe('202504159000001')
        ->and($transactions[0]['transaction_date'])->toBe('2025-04-15')
        ->and($transactions[0]['bank_name'])->toBe(Bank::FOODICS->value)
        ->and($transactions[0]['meta'])->toBeString()
        ->and($transactions[0]['meta'])->toContain('note')
        ->and($transactions[0]['meta'])->toContain('debt payment march')
        ->and($transactions[0]['meta'])->toContain('internal_reference')
        ->and($transactions[0]['meta'])->toContain('A462JE81');

    // Test if the meta-data is parsed correctly and it has key/value pairs
    $meta = json_decode($transactions[0]['meta'], true);
    expect($meta)->toHaveCount(2)
        ->and($meta['note'])->toBe('debt payment march')
        ->and($meta['internal_reference'])->toBe('A462JE81');
});

it('maps the line to transaction DTO', function () {
    $parser = new FoodicsBankParser();

    $webhookData = "20250415156,50#202504159000001#note/debt payment march/internal_reference/A462JE81";

    $transaction = $parser->mapLineToTransaction($webhookData);

    expect($transaction)
        ->toBeInstanceOf(TransactionData::class);
});

it('parses valid webhook with multiple transaction', function () {
    $parser = new FoodicsBankParser();

    $webhookData = "20250415156,50#202504159000001#note/debt payment march/internal_reference/A462JE81\n".
        "20250416200,00#202504169000002#note/salary payment";

    $transactions = $parser->parseTransactions($webhookData);

    expect($transactions)->toHaveCount(2);

    $firstTransaction = $transactions[0];
    expect($firstTransaction['amount'])->toBe(156.50)
        ->and($firstTransaction['reference'])->toBe('202504159000001')
        ->and($firstTransaction['transaction_date'])->toBe('2025-04-15')
        ->and($firstTransaction['bank_name'])->toBe(Bank::FOODICS->value)
        ->and($firstTransaction['meta'])->toBeString()
        ->and($firstTransaction['meta'])->toContain('note')
        ->and($firstTransaction['meta'])->toContain('debt payment march')
        ->and($firstTransaction['meta'])->toContain('internal_reference')
        ->and($firstTransaction['meta'])->toContain('A462JE81');

    $secondTransaction = $transactions[1];
    expect($secondTransaction['amount'])->toBe(200.00)
        ->and($secondTransaction['reference'])->toBe('202504169000002')
        ->and($secondTransaction['transaction_date'])->toBe('2025-04-16')
        ->and($secondTransaction['bank_name'])->toBe(Bank::FOODICS->value)
        ->and($secondTransaction['meta'])->toBeString()
        ->and($secondTransaction['meta'])->toContain('note')
        ->and($secondTransaction['meta'])->toContain('salary payment');
});

it('skips the invalid lines', function () {
    $parser = new FoodicsBankParser();

    $webhookData = "20250415156,50#202504159000001#note/debt payment march/internal_reference/A462JE81\n".
        "invalid transaction\n".
        "20250416200,00#202504169000002#note/salary payment\n".
        "20251315156,50";

    $transactions = $parser->parseTransactions($webhookData);

    expect($transactions)->toHaveCount(2);

    $firstTransaction = $transactions[0];
    expect($firstTransaction['amount'])->toBe(156.50)
        ->and($firstTransaction['reference'])->toBe('202504159000001')
        ->and($firstTransaction['transaction_date'])->toBe('2025-04-15')
        ->and($firstTransaction['bank_name'])->toBe(Bank::FOODICS->value)
        ->and($firstTransaction['meta'])->toBeString()
        ->and($firstTransaction['meta'])->toContain('note')
        ->and($firstTransaction['meta'])->toContain('debt payment march')
        ->and($firstTransaction['meta'])->toContain('internal_reference')
        ->and($firstTransaction['meta'])->toContain('A462JE81');

    $secondTransaction = $transactions[1];
    expect($secondTransaction['amount'])->toBe(200.00)
        ->and($secondTransaction['reference'])->toBe('202504169000002')
        ->and($secondTransaction['transaction_date'])->toBe('2025-04-16')
        ->and($secondTransaction['bank_name'])->toBe(Bank::FOODICS->value)
        ->and($secondTransaction['meta'])->toBeString()
        ->and($secondTransaction['meta'])->toContain('note')
        ->and($secondTransaction['meta'])->toContain('salary payment');
});

it('skips lines with dates in the future', function () {
    $parser = new FoodicsBankParser();

    $webhookData = "20250615156,50#202506159000001#note/debt payment march/internal_reference/A462JE81\n".
        "20250416200,00#202506169000002#note/salary payment\n";

    $transactions = $parser->parseTransactions($webhookData);

    expect($transactions)->toHaveCount(1);

    $firstTransaction = $transactions[0];
    expect($firstTransaction['amount'])->toBe(200.00)
        ->and($firstTransaction['reference'])->toBe('202506169000002')
        ->and($firstTransaction['transaction_date'])->toBe('2025-04-16');
});

it('handles empty webhook', function () {
    $parser = new FoodicsBankParser();
    $webhookData = "";
    $transactions = $parser->parseTransactions($webhookData);

    expect($transactions)->toBeEmpty();
});

it('returns the correct bank name', function () {
    $parser = new FoodicsBankParser();
    expect($parser->getBankName())->toBeString()
        ->toEqual(Bank::FOODICS->value);
});
