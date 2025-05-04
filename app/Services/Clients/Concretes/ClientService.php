<?php

namespace App\Services\Clients\Concretes;

use App\Models\Client;
use App\Services\Clients\Contracts\ClientServiceContract;

class ClientService implements ClientServiceContract
{
    /**
     * @param  int  $client_id
     * @param  array  $columns
     * @return Client
     */
    public function validateClient(int $client_id, array $columns = []): Client
    {
        /**
         * Alternatively, we could cache client data in Redis to verify client existence without querying the database.
         */
        return Client::query()->select( $columns ?: 'id')->findOrFail($client_id);
    }
}
