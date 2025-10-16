<?php

namespace App\Exceptions;

use App\Services\ResponseService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // For API routes, return a consistent JSON structure
        if ($request->expectsJson() || $request->is('api/*')) {
            /** @var ResponseService $responses */
            $responses = app(ResponseService::class);

            // Validation
            if ($exception instanceof ValidationException) {
                return $responses->validationError($exception->errors(), 'Validation failed');
            }

            // Authentication
            if ($exception instanceof AuthenticationException) {
                return $responses->unauthorized('Authentication required');
            }

            // Authorization
            if ($exception instanceof AuthorizationException) {
                return $responses->forbidden('Insufficient permissions');
            }

            // Not found (route or model)
            if ($exception instanceof NotFoundHttpException || $exception instanceof ModelNotFoundException) {
                return $responses->notFound('Resource not found');
            }

            // Method not allowed
            if ($exception instanceof MethodNotAllowedHttpException) {
                return $responses->methodNotAllowed('Method not allowed for this route');
            }

            // HTTP exceptions and unhandled
            return $responses->handleException($exception);
        }

        // Fallback to default rendering for non-API routes
        return parent::render($request, $exception);
    }
}
