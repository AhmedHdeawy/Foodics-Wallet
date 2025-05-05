<?php

namespace App\Services\Clients\Concretes;

use App\Models\Client;
use App\Repositories\Client\Contracts\ClientRepositoryContract;
use App\Services\Clients\Contracts\ClientServiceContract;
use App\Services\Transactions\Contracts\TransactionServiceContract;

class ClientService implements ClientServiceContract
{
    public function __construct(
        protected TransactionServiceContract $transactionService,
        protected ClientRepositoryContract $clientRepository
    ) {}

    public function validateClient(int $client_id, array $columns = []): Client
    {
        /**
         * Alternatively, we could cache client data in Redis to verify client existence without querying the database.
         *
         * @var Client $client
         */
        $client = $this->clientRepository->findOrFail($client_id, $columns ?: ['id']);

        return $client;
    }

    public function updateBalance(int $client_id): void
    {
        $this->clientRepository->update($client_id, [
            'balance' => $this->transactionService->sumClientBalance($client_id),
        ]);
    }
}
