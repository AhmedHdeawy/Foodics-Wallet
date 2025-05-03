<?php

namespace App\DTOs;

use Carbon\Carbon;

readonly class TransactionData
{
    public function __construct(
        private string $reference,
        private float $amount,
        private Carbon $date,
        private array $meta,
        private string $bank,
        private int $clientId = 1
    ) {}

    public function toArray(): array
    {
        return [
            'reference' => $this->reference,
            'amount' => $this->amount,
            'date' => $this->date,
            'meta' => $this->meta,
            'bank' => $this->bank,
            'client_id' => $this->clientId,
        ];
    }
}
