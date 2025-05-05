<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;

class InsufficientBalance extends Exception
{
    use ApiResponse;

    public function render(): JsonResponse
    {
        return $this->errorResponse('Insufficient balance', 400);
    }
}
