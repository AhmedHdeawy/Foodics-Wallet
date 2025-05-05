<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Exception;

class InsufficientBalance extends Exception
{
    use ApiResponse;

    public function render(): JsonResponse
    {
        return $this->errorResponse('Insufficient balance', 400);
    }
}
