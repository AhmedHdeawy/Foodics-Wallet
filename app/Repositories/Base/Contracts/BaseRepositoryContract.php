<?php

namespace App\Repositories\Base\Contracts;

use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryContract
{
    public function find(int $id, array $columns = ['*']): ?Model;

    public function findByField(string $field, mixed $value, array $columns = ['*']): ?Model;

    public function findOrFail(int $id, array $columns = ['*']): Model;

    public function create(array $data): Model;

    public function update(int $id, array $data): Model;

    public function getModel(): Model;
}
