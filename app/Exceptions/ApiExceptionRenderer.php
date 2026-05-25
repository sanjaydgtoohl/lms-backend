<?php

namespace App\Exceptions;

use App\Services\ResponseService;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * Maps exceptions to the project's standard API JSON envelope.
 *
 * Response shape (unchanged):
 * {
 *   "success": false,
 *   "message": "...",
 *   "errors": null|{...},
 *   "error_code": "UNAUTHORIZED|...",
 *   "meta": { "timestamp": "...", "status_code": 401 }
 * }
 */
class ApiExceptionRenderer
{
    public function __construct(
        protected ResponseService $responses
    ) {
    }

    public function render(Throwable $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return $this->responses->validationError(
                $exception->errors(),
                'Validation failed'
            );
        }

        if ($exception instanceof AuthenticationException) {
            return $this->responses->unauthorized('Authentication required');
        }

        if ($exception instanceof JWTException) {
            return $this->responses->unauthorized(
                $this->safeMessage($exception, 'Unauthorized')
            );
        }

        if ($exception instanceof UnauthorizedHttpException) {
            return $this->responses->unauthorized(
                $this->safeMessage($exception, 'Unauthorized')
            );
        }

        if ($exception instanceof AuthorizationException) {
            return $this->responses->forbidden('Insufficient permissions');
        }

        if ($exception instanceof ModelNotFoundException) {
            return $this->responses->notFound('Resource not found');
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->responses->notFound('API endpoint not found');
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->responses->methodNotAllowed('Method not allowed for this route');
        }

        if ($exception instanceof DomainException) {
            return $this->renderDomainException($exception);
        }

        if ($exception instanceof QueryException) {
            return $this->renderQueryException($exception);
        }

        if ($exception instanceof HttpException) {
            return $this->renderHttpException($exception);
        }

        return $this->renderServerError($exception);
    }

    protected function renderDomainException(DomainException $exception): JsonResponse
    {
        $message = $exception->getMessage() ?: 'Request could not be processed';

        if ($this->isNotFoundMessage($message)) {
            return $this->responses->notFound($message);
        }

        return $this->responses->error(
            $message,
            null,
            ResponseService::HTTP_UNPROCESSABLE_ENTITY,
            'DOMAIN_ERROR'
        );
    }

    protected function renderHttpException(HttpException $exception): JsonResponse
    {
        $status = $exception->getStatusCode();
        $message = $this->safeMessage($exception, $this->defaultMessageForStatus($status));
        $errorCode = $this->errorCodeForStatus($status);

        return $this->responses->error($message, null, $status, $errorCode);
    }

    protected function renderQueryException(QueryException $exception): JsonResponse
    {
        $this->logException($exception);

        $message = $this->isDebug()
            ? 'Database error: ' . $exception->getMessage()
            : 'Database error occurred';

        return $this->responses->serverError($message);
    }

    protected function renderServerError(Throwable $exception): JsonResponse
    {
        $this->logException($exception);

        $message = $this->isDebug()
            ? $exception->getMessage() ?: 'An unexpected error occurred'
            : 'An unexpected error occurred';

        return $this->responses->serverError($message);
    }

    protected function errorCodeForStatus(int $status): string
    {
        return match ($status) {
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            405 => 'METHOD_NOT_ALLOWED',
            422 => 'VALIDATION_ERROR',
            500 => 'SERVER_ERROR',
            default => 'HTTP_ERROR',
        };
    }

    protected function defaultMessageForStatus(int $status): string
    {
        return match ($status) {
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Resource not found',
            405 => 'Method not allowed',
            422 => 'Validation failed',
            429 => 'Too many requests',
            500 => 'Internal server error',
            default => 'HTTP error occurred',
        };
    }

    protected function isNotFoundMessage(string $message): bool
    {
        return (bool) preg_match('/\bnot found\b/i', $message);
    }

    protected function safeMessage(Throwable $exception, string $fallback): string
    {
        $message = trim($exception->getMessage());

        return $message !== '' ? $message : $fallback;
    }

    protected function isDebug(): bool
    {
        return filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN);
    }

    protected function logException(Throwable $exception): void
    {
        report($exception);
    }
}
