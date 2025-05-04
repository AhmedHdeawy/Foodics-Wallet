<?php

namespace App\Services\BankParsers\Concretes;

use App\DTOs\TransactionData;
use App\Enums\Bank;
use App\Services\BankParsers\Contracts\BankParserContract;
use App\Services\BankParsers\Contracts\MapLineToTransactionContract;
use App\Traits\ParserChecks;
use Carbon\Carbon;
use Exception;

class AcmeBankParser implements BankParserContract, MapLineToTransactionContract
{
    use ParserChecks;

    /**
     * Format: Amount (two decimals), "//", Reference, "//", Date
     * Example: 156,50//202506159000001//20250615
     *
     * @param  string  $webhookData  Raw webhook data
     * @return array Array of parsed transactions
     */
    public function parseTransactions(string $webhookData): array
    {
        $transactions = [];
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
        $this->checkLineHasTwoSlashesCharacter($line);

        // Split by // separator
        $parts = explode('//', $line);
        $this->checkPartsCount($parts, $line, 3);

        $amount = $this->prepareAmount($parts[0]);
        $reference = $parts[1];
        $date = $this->parseDate($parts[2]);

        return new TransactionData($reference, $amount, $date, $this->getBankName());
    }

    public function getBankName(): string
    {
        return Bank::ACME->value;
    }

    private function parseDate(string $value): Carbon
    {
        $this->checkDateValueLength($value);

        $date = Carbon::parse($value);

        $this->checkIfDateInTheFuture($date);

        return $date;
    }

    private function prepareAmount(string $value): float
    {
        // Extract amount (replacing comma with dot for decimal)
        $amountStr = $value;
        $amount = (float) str_replace(',', '.', $amountStr);
        $this->checkNegativeAmount($amount);

        return $amount;
    }
}
