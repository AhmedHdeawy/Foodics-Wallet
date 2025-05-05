<?php

namespace App\Repositories\Client\Concretes;

use App\Models\Client;
use App\Repositories\Base\Concretes\BaseRepository;
use App\Repositories\Client\Contracts\ClientRepositoryContract;

class ClientRepository extends BaseRepository implements ClientRepositoryContract
{
    /**
     * Specify Model class name
     */
    protected function model(): string
    {
        return Client::class;
    }

}
