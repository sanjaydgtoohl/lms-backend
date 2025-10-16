<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class ResponseService
{
    /**
     * Success response
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public function success($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'status_code' => $statusCode,
            ]
        ], $statusCode);
    }

    /**
     * Error response
     *
     * @param string $message
     * @param mixed $errors
     * @param int $statusCode
     * @param string $errorCode
     * @return JsonResponse
     */
    public function error(string $message = 'Error', $errors = null, int $statusCode = 400, string $errorCode = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'error_code' => $errorCode,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'status_code' => $statusCode,
            ]
        ], $statusCode);
    }

    /**
     * Validation error response
     *
     * @param array $errors
     * @param string $message
     * @return JsonResponse
     */
    public function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->error($message, $errors, 422, 'VALIDATION_ERROR');
    }

    /**
     * Unauthorized response
     *
     * @param string $message
     * @return JsonResponse
     */
    public function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, null, 401, 'UNAUTHORIZED');
    }

    /**
     * Forbidden response
     *
     * @param string $message
     * @return JsonResponse
     */
    public function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, null, 403, 'FORBIDDEN');
    }

    /**
     * Not found response
     *
     * @param string $message
     * @return JsonResponse
     */
    public function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, null, 404, 'NOT_FOUND');
    }

    /**
     * Method not allowed response
     *
     * @param string $message
     * @return JsonResponse
     */
    public function methodNotAllowed(string $message = 'Method not allowed'): JsonResponse
    {
        return $this->error($message, null, 405, 'METHOD_NOT_ALLOWED');
    }

    /**
     * Server error response
     *
     * @param string $message
     * @param mixed $errors
     * @return JsonResponse
     */
    public function serverError(string $message = 'Internal server error', $errors = null): JsonResponse
    {
        return $this->error($message, $errors, 500, 'SERVER_ERROR');
    }

    /**
     * Created response
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    public function created($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Updated response
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    public function updated($data = null, string $message = 'Resource updated successfully'): JsonResponse
    {
        return $this->success($data, $message, 200);
    }

    /**
     * Deleted response
     *
     * @param string $message
     * @return JsonResponse
     */
    public function deleted(string $message = 'Resource deleted successfully'): JsonResponse
    {
        return $this->success(null, $message, 200);
    }

    /**
     * Paginated response
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    public function paginated($data, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return $this->success($data, $message, 200);
    }

    /**
     * Handle exception and return appropriate response
     *
     * @param Throwable $exception
     * @return JsonResponse
     */
    public function handleException(Throwable $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return $this->validationError($exception->errors(), 'Validation failed');
        }

        if ($exception instanceof AuthenticationException) {
            return $this->unauthorized('Authentication required');
        }

        if ($exception instanceof AuthorizationException) {
            return $this->forbidden('Insufficient permissions');
        }

        if ($exception instanceof ModelNotFoundException) {
            return $this->notFound('Resource not found');
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->notFound('Route not found');
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->methodNotAllowed('Method not allowed for this route');
        }

        if ($exception instanceof HttpException) {
            return $this->error(
                $exception->getMessage() ?: 'HTTP error occurred',
                null,
                $exception->getStatusCode(),
                'HTTP_ERROR'
            );
        }

        // Log the exception for debugging
        \Log::error('Unhandled exception: ' . $exception->getMessage(), [
            'exception' => $exception,
            'trace' => $exception->getTraceAsString()
        ]);

        return $this->serverError('An unexpected error occurred');
    }

    /**
     * API response with consistent structure
     *
     * @param bool $success
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     * @param string $errorCode
     * @return JsonResponse
     */
    public function apiResponse(
        bool $success,
        $data = null,
        string $message = '',
        int $statusCode = 200,
        $errors = null,
        string $errorCode = null
    ): JsonResponse {
        $response = [
            'success' => $success,
            'message' => $message,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'status_code' => $statusCode,
            ]
        ];

        if ($success) {
            $response['data'] = $data;
        } else {
            $response['errors'] = $errors;
            $response['error_code'] = $errorCode;
        }

        return response()->json($response, $statusCode);
    }
}
