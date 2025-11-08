<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ResponseService;

class RoleMiddleware
{
    /**
     * The response service instance
     *
     * @var ResponseService
     */
    protected $responseService;

    /**
     * Constructor
     *
     * @param ResponseService $responseService
     */
    public function __construct(ResponseService $responseService)
    {
        $this->responseService = $responseService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user ?? $request->user();

        if (!$user) {
            return $this->responseService->unauthorized('Unauthenticated');
        }

        if (!$user->hasRole($role)) {
            return $this->responseService->forbidden('Insufficient permissions. Required role: ' . $role);
        }

        return $next($request);
    }
}
