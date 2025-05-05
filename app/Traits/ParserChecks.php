<?php

namespace App\Traits;

use Carbon\Carbon;
use InvalidArgumentException;

trait ParserChecks
{
    public function checkLineHasTwoHashCharacter(string $line): void
    {
        if (substr_count($line, '#') < 2) {
            throw new InvalidArgumentException('Invalid transaction format: '.$line);
        }
    }

    public function checkLineHasTwoSlashesCharacter(string $line): void
    {
        if (substr_count($line, '//') < 2) {
            throw new InvalidArgumentException('Invalid transaction format: '.$line);
        }
    }

    public function checkPartsCount(array $parts, string $line, int $count = 2): void
    {
        if (count($parts) < $count) {
            throw new InvalidArgumentException('Invalid transaction format: '.$line);
        }
    }

    public function checkDateAmountPart(string $part): void
    {
        if (strlen($part) < 9 || ! str_contains($part, ',')) {
            throw new InvalidArgumentException('Date and amount part is invalid: '.$part);
        }
    }

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

    public function checkNegativeAmount(float $amount): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative: '.$amount);
        }
    }

    public function isValidReference(string $reference): void
    {
        // Check if the reference is empty or contains invalid characters
        if (empty($reference) ||
            (preg_match('/^\d+[,.]\d+$/', $reference)) ||
            (str_contains($reference, ' '))
        ) {
            throw new InvalidArgumentException('Invalid reference format: '.$reference);
        }
    }
}
