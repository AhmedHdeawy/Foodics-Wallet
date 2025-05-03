<?php

namespace App\DTOs;

use Carbon\Carbon;

readonly class TransactionData
{
    public function __construct(
        private string $reference,
        private float $amount,
        private Carbon $date,
        private string $bank,
        private ?array $meta = null,
        private int $clientId = 1,
    ) {}

    public function toArray(): array
    {
        return [
            'reference' => $this->reference,
            'amount' => $this->amount,
            'date' => $this->date,
            'bank' => $this->bank,
            'meta' => $this->meta,
            'client_id' => $this->clientId,
        ];
    }
}
