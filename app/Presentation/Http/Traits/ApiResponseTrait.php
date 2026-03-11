<?php

namespace App\Presentation\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    protected function success(mixed $data, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'meta'    => ['timestamp' => now()->toISOString()],
        ], $status);
    }

    protected function created(mixed $data, string $message = 'Resource created successfully.'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    protected function error(string $message, array $errors = [], int $status = 400): JsonResponse
    {
        $body = [
            'success' => false,
            'message' => $message,
            'data'    => null,
            'meta'    => ['timestamp' => now()->toISOString()],
        ];

        if (! empty($errors)) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }
}

