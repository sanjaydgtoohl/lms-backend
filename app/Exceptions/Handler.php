<?php

namespace App\Exceptions;

use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;

class Handler extends ExceptionHandler
{
    /**
     * Exceptions that should not be reported to the logs.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        JWTException::class,
        DomainException::class,
    ];

    /**
     * Report or log an exception.
     */
    public function report(Throwable $exception): void
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        if ($this->shouldRenderAsApi($request)) {
            return app(ApiExceptionRenderer::class)->render($exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Determine if the request should receive the standard API JSON error envelope.
     */
    protected function shouldRenderAsApi($request): bool
    {
        if (!$request instanceof Request) {
            return false;
        }

        if ($request->expectsJson()) {
            return true;
        }

        $path = ltrim($request->path(), '/');

        return str_starts_with($path, 'api/')
            || str_starts_with($path, 'v1/');
    }
}
