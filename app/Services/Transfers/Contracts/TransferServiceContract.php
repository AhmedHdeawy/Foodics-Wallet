<?php

namespace App\Services\Transfers\Contracts;

interface TransferServiceContract
{
    public function transferMoney(array $data): string;
}
