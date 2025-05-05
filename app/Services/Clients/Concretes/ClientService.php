<?php

namespace App\Services\Clients\Concretes;

use App\Models\Client;
use App\Services\Clients\Contracts\ClientServiceContract;
use App\Services\Transactions\Contracts\TransactionServiceContract;

class ClientService implements ClientServiceContract
{

    public function __construct(
        protected TransactionServiceContract $transactionService,
    ) {
    }

    public function validateClient(int $client_id, array $columns = []): Client
    {
        /**
         * Alternatively, we could cache client data in Redis to verify client existence without querying the database.
         */
        return Client::query()->select($columns ?: 'id')->findOrFail($client_id);
    }

    public function updateBalance(int $client_id): void
    {
        Client::query()->where('id', $client_id)->update([
            'balance' => $this->transactionService->sumClientBalance($client_id),
        ]);
    }
}
