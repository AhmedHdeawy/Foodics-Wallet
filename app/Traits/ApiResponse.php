<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    /**
     * Return a success JSON response.
     */
    public function successResponse(mixed $data, int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json(['data' => $data], $code);
    }

    /**
     * Return an error JSON response.
     */
    public function errorResponse($message, int $code): JsonResponse
    {
        return response()->json(['error' => $message], $code);
    }
}
