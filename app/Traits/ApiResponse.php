<?php

namespace App\Traits;

use Illuminate\Http\Response; // HTTP status codes ke liye

/**
 * Yeh Trait humein apne custom JSON format mein
 * response bhejne mein help karega.
 */
trait ApiResponse
{
    /**
     * Ek successful response ka format.
     *
     * @param mixed $data    (Jo data bhejna hai)
     * @param string $message (Success ka message)
     * @param int $statusCode (Jaise 200 OK, 201 Created)
     */
    public function successResponse($data, $message = 'Success', $statusCode = Response::HTTP_OK)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'meta' => [
                'timestamp'   => \Carbon\Carbon::now()->toIso8601String(), // Current ISO time
                'status_code' => $statusCode
            ],
            'data'    => $data,
        ], $statusCode);
    }

    /**
     * Ek error response ka format.
     *
     * @param string $message  (Error ka message)
     * @param int $statusCode (Jaise 404 Not Found, 422 Unprocessable)
     */
    public function errorResponse($message, $statusCode)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'meta' => [
                'timestamp'   =>\Carbon\Carbon::now()->toIso8601String(),
                'status_code' => $statusCode
            ],
            'data'    => null, // Error mein data null rahega
        ], $statusCode);
    }
}