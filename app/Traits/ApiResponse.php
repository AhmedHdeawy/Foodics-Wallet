<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Response as HttpResponse;

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

    /**
     * Return a success XML response.
     */
    public function xmlResponse(mixed $xml, int $code = Response::HTTP_OK): HttpResponse
    {
        return response($xml, $code)->header('Content-Type', 'application/xml');
    }
}
