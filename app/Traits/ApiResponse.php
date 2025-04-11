<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse as JsonResponseAlias;

trait ApiResponse
{
    /**
     * Send a success response
     *
     * @param string|array $data
     * @param string $message
     * @param int $status
     * @return JsonResponseAlias
     */
    public function successResponse($data = [], $message = 'Success', $status = 200): JsonResponseAlias
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Send an error response
     *
     * @param string|array $errors
     * @param string $message
     * @param int $status
     * @return JsonResponseAlias
     */
    public function errorResponse($errors = [], $message = 'Error', $status = 422): JsonResponseAlias
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
