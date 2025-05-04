<?php

namespace App\Traits;

use Carbon\Carbon;
use InvalidArgumentException;

trait ParserChecks
{
    public function checkDateValueLength(string $value): void
    {
        if (strlen($value) < 8) {
            throw new InvalidArgumentException('Date format is invalid: '.$value);
        }
    }

    public function checkIfDateInTheFuture(Carbon $date): void
    {
        if ($date->greaterThan(Carbon::today())) {
            throw new InvalidArgumentException('Date cannot be in the future: '.$date);
        }
    }

    public function checkIfAmountLessThanZero(float $amount): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative: '.$amount);
        }
    }
}
