<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\JsonResponse;

trait RespondsWithApi
{
    protected function success(mixed $data = null, string $message = 'Success', int $status = 200, array $meta = []): JsonResponse
    {
        return response()->json(array_filter(['status' => $status, 'message' => $message, 'data' => $data, 'meta' => $meta ?: null], fn ($v) => $v !== null), $status);
    }

    protected function error(string|array $message, int $status = 400): JsonResponse
    {
        return response()->json(['status' => $status, 'message' => $message], $status);
    }
}
