<?php

namespace App\Services\BankParsers\Concretes;

use App\Enums\Bank;
use App\Services\BankParsers\Contracts\BankParserContract;

class BankParserFactory
{
    /**
     * Get a parser for the specified bank
     */
    public function getParser(Bank $bank): BankParserContract
    {
        return match ($bank) {
            Bank::ACME => new AcmeBankParser,
            Bank::FOODICS => new FoodicsBankParser
        };
    }
}
