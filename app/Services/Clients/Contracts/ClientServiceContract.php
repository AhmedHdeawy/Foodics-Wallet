<?php

namespace App\Services\Clients\Contracts;

use App\Models\Client;

interface ClientServiceContract
{
    public function validateClient(int $client_id, array $columns = []): Client;
}
