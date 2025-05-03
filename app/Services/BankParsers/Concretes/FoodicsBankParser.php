<?php

namespace App\Services\BankParsers\Concretes;

use App\DTOs\TransactionData;
use App\Enums\Bank;
use App\Services\BankParsers\Contracts\BankParserContract;
use Carbon\Carbon;
use Exception;
use InvalidArgumentException;

class FoodicsBankParser implements BankParserContract
{
    /**
     * Format: Date, Amount (two decimals), "#", Reference, "#", Key-value pairs
     * Example: 20250615156,50#202506159000001#note/debt payment march/internal_reference/A462JE81
     *
     * @param  string  $webhookData  Raw webhook data
     * @return TransactionData[] Array of parsed transactions
     */
    public function parseTransactions(string $webhookData): array
    {
        $result = [];
        $lines = explode("\n", trim($webhookData));

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            try {
                // Split by # separator
                $parts = explode('#', $line);
                if (count($parts) < 2) {
                    continue; // Invalid format, (Assumed the metadata part(3) is optional)
                }

                $date = $this->parseDate($parts[0]);
                $amount = $this->prepareAmount($parts[0]);
                $reference = $parts[1];
                $meta = $this->parseMetaData($parts);

                $result[] = new TransactionData($reference, $amount, $date, $meta, $this->getBankName());
            } catch (Exception $e) {
                // Later, we can store incorrect transactions in a database for further analysis.
                logger()->error("Error parsing Foodics transaction: {$e->getMessage()}",
                    [
                        'transaction' => $line,
                        'error' => $e->getMessage(),
                    ]);

                continue;
            }
        }

        return $result;
    }

    public function getBankName(): string
    {
        return Bank::FOODICS->value;
    }

    private function parseDate(string $value): Carbon
    {
        if (strlen($value) < 8) {
            throw new InvalidArgumentException('Date format is invalid: '.$value);
        }

        try {
            $date = substr($value, 0, 8);

            return Carbon::parse($date);
        } catch (Exception) {
            throw new InvalidArgumentException("Cannot parse date: $value");
        }
    }

    private function prepareAmount(string $value): float
    {
        $amount = substr($value, 8);
        $amount = (float) str_replace(',', '.', $amount);
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative: '.$amount);
        }

        return $amount;
    }

    private function parseMetaData(array $parts): array
    {
        $meta = [];
        try {
            if (isset($parts[2])) {
                $keyValuePairs = explode('/', $parts[2]);
                $totalPairs = count($keyValuePairs);

                for ($i = 0; $i < $totalPairs; $i += 2) {
                    if (isset($keyValuePairs[$i + 1])) {
                        $key = $keyValuePairs[$i];
                        $value = $keyValuePairs[$i + 1];
                        $meta[$key] = $value;
                    }
                }
            }
        } catch (Exception $e) {
            logger()->error("Error parsing Foodics meta data: {$e->getMessage()}");
        }

        return $meta;
    }
}
