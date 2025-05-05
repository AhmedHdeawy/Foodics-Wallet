<?php

namespace App\DTOs;

use App\Enums\TransactionStatus;
use Carbon\Carbon;

readonly class TransactionData
{
    public function __construct(
        private string $reference,
        private float $amount,
        private Carbon $transaction_date,
        private string $bank_name,
        private array|string|null $meta = null,
        private int $clientId = 1,
    ) {}

    public function toArray(): array
    {
        return [
            'reference' => $this->reference,
            'amount' => $this->amount,
            'transaction_date' => $this->transaction_date,
            'bank_name' => $this->bank_name,
            'meta' => $this->meta,
            'client_id' => $this->clientId,
        ];
    }

    public function toBatchInsertRows(): array
    {
        return [
            'reference' => $this->reference,
            'amount' => $this->amount,
            'transaction_date' => $this->getDateFormatted(),
            'bank_name' => $this->bank_name,
            'meta' => is_array($this->meta) ? json_encode($this->meta) : $this->meta,
            'client_id' => $this->clientId,
            'status' => TransactionStatus::COMPLETED->value,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
            'unique_identifier' => $this->generateUniqueIdentifier(),
        ];
    }

    public function getDateFormatted(): string
    {
        return $this->transaction_date->format('Y-m-d');
    }

    /**
     * Generate a unique identifier for this transaction
     * This helps prevent duplicate transactions
     * Combines bank name, reference and transaction date
     */
    public function generateUniqueIdentifier(): string
    {
        return md5($this->clientId.$this->bank_name.$this->reference.$this->getDateFormatted().$this->amount);
    }
}
