<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Carbon\Carbon;
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
     * Common HTTP status codes
     */
    public const HTTP_OK = 200;
    public const HTTP_CREATED = 201;
    public const HTTP_ACCEPTED = 202;
    public const HTTP_NO_CONTENT = 204;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_METHOD_NOT_ALLOWED = 405;
    public const HTTP_UNPROCESSABLE_ENTITY = 422;
    public const HTTP_SERVER_ERROR = 500;

    /**
     * Success response
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public function success($data = null, string $message = 'Success', int $statusCode = self::HTTP_OK): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
            'meta' => [
                'timestamp' => Carbon::now()->toISOString(),
                'status_code' => $statusCode,
            ]
        ];

        // Attach data
        $payload['data'] = $this->transformDataWithPagination($data, $payload['meta']);

        return response()->json($payload, $statusCode);
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
   public function error(string $message = 'Error',?array $errors = null,int $statusCode = self::HTTP_BAD_REQUEST,?string $errorCode = null): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'error_code' => $errorCode,
            'meta' => [
                'timestamp' => Carbon::now()->toISOString(),
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
        return $this->error($message, $errors, self::HTTP_UNPROCESSABLE_ENTITY, 'VALIDATION_ERROR');
    }

    /**
     * Unauthorized response
     *
     * @param string $message
     * @return JsonResponse
     */
    public function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, null, self::HTTP_UNAUTHORIZED, 'UNAUTHORIZED');
    }

    /**
     * Forbidden response
     *
     * @param string $message
     * @return JsonResponse
     */
    public function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, null, self::HTTP_FORBIDDEN, 'FORBIDDEN');
    }

    /**
     * Not found response
     *
     * @param string $message
     * @return JsonResponse
     */
    public function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, null, self::HTTP_NOT_FOUND, 'NOT_FOUND');
    }

    /**
     * Method not allowed response
     *
     * @param string $message
     * @return JsonResponse
     */
    public function methodNotAllowed(string $message = 'Method not allowed'): JsonResponse
    {
        return $this->error($message, null, self::HTTP_METHOD_NOT_ALLOWED, 'METHOD_NOT_ALLOWED');
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
        return $this->error($message, $errors, self::HTTP_SERVER_ERROR, 'SERVER_ERROR');
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
        return $this->success($data, $message, self::HTTP_CREATED);
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
        return $this->success($data, $message, self::HTTP_OK);
    }

    /**
     * Deleted response
     *
     * @param string $message
     * @return JsonResponse
     */
    public function deleted(string $message = 'Resource deleted successfully'): JsonResponse
    {
        return $this->success(null, $message, self::HTTP_OK);
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
        return $this->success($data, $message, self::HTTP_OK);
    }

    /**
     * Accepted response (202)
     */
    public function accepted($data = null, string $message = 'Accepted'): JsonResponse
    {
        return $this->success($data, $message, self::HTTP_ACCEPTED);
    }

    /**
     * No content response (204)
     */
    public function noContent(): JsonResponse
    {
        return response()->json(null, self::HTTP_NO_CONTENT);
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
     * @param mixed|null $data
     * @param string $message
     * @param int $statusCode
     * @param mixed|null $errors
     * @param string|null $errorCode
     * @return JsonResponse
     */
    public function apiResponse(
        bool $success,
        mixed $data = null,           // explicitly nullable
        string $message = '',
        int $statusCode = self::HTTP_OK,
        mixed $errors = null,         // explicitly nullable
        ?string $errorCode = null     // explicitly nullable
    ): JsonResponse {
        $response = [
            'success' => $success,
            'message' => $message,
            'meta' => [
                'timestamp' => Carbon::now()->toISOString(),
                'status_code' => $statusCode,
            ]
        ];
        
        if ($success) {
            $response['data'] = $this->transformDataWithPagination($data, $response['meta']);
        } else {
            $response['errors'] = $errors;
            $response['error_code'] = $errorCode;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * If resource is a paginator, attach pagination meta automatically.
     */
    protected function transformDataWithPagination($data, array &$meta)
    {
        if ($data instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            $meta['pagination'] = [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ];
            return $data->items();
        }

        if ($data instanceof \Illuminate\Contracts\Pagination\Paginator) {
            $meta['pagination'] = [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'has_more_pages' => $data->hasMorePages(),
            ];
            return $data->items();
        }

        return $data;
    }
}