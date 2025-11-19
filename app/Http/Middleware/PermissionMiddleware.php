<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ResponseService;

class PermissionMiddleware
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
     * @param  string|array  $permissions
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permissions)
    {
        $user = $request->user ?? $request->user();

        if (!$user) {
            return $this->responseService->unauthorized('Unauthenticated');
        }

        // Convert single permission to array
        if (is_string($permissions)) {
            $permissions = [$permissions];
        }

        // Check if user has any of the required permissions
        if (!$user->hasAnyPermission($permissions)) {
            $permissionsList = implode(', ', $permissions);
            return $this->responseService->forbidden('Insufficient permissions. Required permission(s): ' . $permissionsList);
        }

        return $next($request);
    }
}

