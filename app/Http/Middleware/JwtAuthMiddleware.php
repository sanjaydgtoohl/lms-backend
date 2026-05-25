<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use App\Models\User;
use App\Services\ResponseService;

class JwtAuthMiddleware
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
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Try to authenticate the user
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user) {
                return $this->responseService->unauthorized('User not found');
            }

            // Check if user is active
            if (!$user->isActive()) {
                return $this->responseService->unauthorized('User account is inactive');
            }

            $this->setAuthenticatedUser($request, $user);

        } catch (TokenExpiredException $e) {
            // For refresh endpoint, allow expired tokens to pass through
            if ($request->route() && ($request->route()->getName() === 'auth.refresh' || 
                $request->is('api/v1/auth/refresh'))) {
                // Try to get user from expired token payload
                try {
                    $payload = JWTAuth::parseToken()->getPayload();
                    $userId = $payload->get('sub');
                    $user = User::find($userId);
                    
                    if ($user && $user->isActive()) {
                        $this->setAuthenticatedUser($request, $user);
                        return $next($request);
                    }
                } catch (\Exception $e) {
                    // Continue with normal error handling
                }
            }
            
            return $this->responseService->unauthorized('Token has expired');

        } catch (TokenInvalidException $e) {
            return $this->responseService->unauthorized('Token is invalid');

        } catch (JWTException $e) {
            return $this->responseService->unauthorized('Token is required');
        }

        return $next($request);
    }

    /**
     * Attach the JWT user to the request and Laravel auth guard.
     */
    protected function setAuthenticatedUser(Request $request, User $user): void
    {
        $request->setUserResolver(fn () => $user);
        $request->merge(['user' => $user]);
        Auth::setUser($user);
        JWTAuth::setUser($user);
    }
}
