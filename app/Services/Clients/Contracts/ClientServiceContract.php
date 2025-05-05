<?php

namespace App\Services\Clients\Contracts;

use App\Models\Client;

interface ClientServiceContract
{
    public function validateClient(int $client_id, array $columns = []): Client;
    public function updateBalance(int $client_id): void;
}
