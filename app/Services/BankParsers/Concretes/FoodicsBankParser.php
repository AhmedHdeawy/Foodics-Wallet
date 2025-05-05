<?php

namespace App\Services\BankParsers\Concretes;

use App\DTOs\TransactionData;
use App\Enums\Bank;
use App\Services\BankParsers\Contracts\BankParserContract;
use App\Services\BankParsers\Contracts\MapLineToTransactionContract;
use App\Traits\ParserChecks;
use Carbon\Carbon;
use Exception;
use InvalidArgumentException;

class FoodicsBankParser implements BankParserContract, MapLineToTransactionContract
{
    use ParserChecks;

    protected int $clientId;

    /**
     * Format: Date, Amount (two decimals), "#", Reference, "#", Key-value pairs
     * Example: 20250615156,50#202506159000001#note/debt payment march/internal_reference/A462JE81
     *
     * @param  string  $webhookData  Raw webhook data
     * @param  int  $clientId
     * @return array of parsed transactions
     */
    public function parseTransactions(string $webhookData, int $clientId): array
    {
        $transactions = [];
        $this->clientId = $clientId;
        $lines = explode("\n", trim($webhookData));

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            try {
                $transactions[] = $this->mapLineToTransaction($line)->toBatchInsertRows();
            } catch (Exception $e) {
                // Later, we can store incorrect transactions in a database for further analysis.
                logger()->error("Error parsing {$this->getBankName()} transaction: {$e->getMessage()}",
                    [
                        'transaction' => $line,
                        'error' => $e->getMessage(),
                    ]);

                continue;
            }
        }

        return $transactions;
    }

    public function mapLineToTransaction(string $line): TransactionData
    {
        $this->checkLineHasTwoHashCharacter($line);

        // Split by # separator
        $parts = explode('#', $line);
        $this->checkPartsCount($parts, $line);
        $this->checkDateAmountPart($parts[0]);

        $date = $this->parseDate($parts[0]);
        $amount = $this->prepareAmount($parts[0]);
        $reference = $parts[1];
        $meta = $this->parseMetaData($parts);

        return new TransactionData($reference, $amount, $date, $this->getBankName(), $meta, $this->clientId);
    }

    public function getBankName(): string
    {
        return Bank::FOODICS->value;
    }

    private function parseDate(string $value): Carbon
    {
        $this->checkDateValueLength($value);

        try {
            $date = Carbon::parse(substr($value, 0, 8));

            $this->checkIfDateInTheFuture($date);

            return $date;
        } catch (Exception) {
            throw new InvalidArgumentException("Cannot parse date: $value");
        }
    }

    private function prepareAmount(string $value): float
    {
        $amount = substr($value, 8);
        $amount = (float) str_replace(',', '.', $amount);
        $this->checkNegativeAmount($amount);

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
