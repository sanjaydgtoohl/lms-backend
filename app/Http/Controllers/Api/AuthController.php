<?php

namespace App\Http\Controllers\Api;

use App\Models\LoginLog;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\ResponseService;
use App\Http\Resources\AuthResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * The auth service instance
     *
     * @var AuthService
     */
    protected $authService;

    /**
     * The response service instance
     *
     * @var ResponseService
     */
    protected $responseService;

    /**
     * Constructor
     *
     * @param AuthService $authService
     * @param ResponseService $responseService
     */
    public function __construct(AuthService $authService, ResponseService $responseService)
    {
        $this->authService = $authService;
        $this->responseService = $responseService;
    }

    /**
     * Register a new user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->all());
            
            return $this->responseService->created(
                new AuthResource($result['user'], $result['token'], $result['token_type'], $result['expires_in']),
                'User registered successfully'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Registration validation failed');
        } catch (\Exception $e) {
            return $this->responseService->serverError('Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Login user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->all());

            // --- YAHAN LOGGING LOGIC ADD KAREIN ---
            if (isset($result['user'])) {
                /** @var \App\Models\User $user */
                $user = $result['user'];

                // (Optional) Purane "active" sessions ko logout mark karein
             $user->loginLogs()
                 ->whereNull('logout_time')
                 ->update(['logout_time' => \Carbon\Carbon::now()]);

                // Naya log banayein
                LoginLog::create([
                    'user_id' => $user->id,
                    'login_time' => \Carbon\Carbon::now(),
                    'login_data' => [
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->header('User-Agent'),
                    ],
                ]);
            }
            // --- LOGGING LOGIC END ---
            
            return $this->responseService->success(
                new AuthResource($result['user'], $result['token'], $result['token_type'], $result['expires_in']),
                'Login successful'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Login validation failed');
        } catch (\Exception $e) {
            return $this->responseService->serverError('Login failed: ' . $e->getMessage());
        }
    }

    /**
     * Logout user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // --- LOGGING LOGIC ADD KAREIN (Logout se pehle) ---
            /** @var \App\Models\User|null $user */
            $user = Auth::user(); // User ko logout karne se pehle lein

            if ($user) {
                // User ka sabse latest active log dhundein
                $log = $user->loginLogs()
                           ->whereNull('logout_time')
                           ->latest('login_time')
                           ->first();

                // Agar mila hai, toh uska logout time update karein
                if ($log) {
                    $log->update(['logout_time' => \Carbon\Carbon::now()]);
                }
            }
            // --- LOGGING LOGIC END ---

            $success = $this->authService->logout();
            
            if ($success) {
                return $this->responseService->success(null, 'Logout successful');
            }
            
            return $this->responseService->serverError('Logout failed');
        } catch (\Exception $e) {
            return $this->responseService->serverError('Logout failed: ' . $e->getMessage());
        }
    }

    /**
     * Refresh token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->refresh();
            
            return $this->responseService->success(
                new AuthResource($result['user'], $result['token'], $result['token_type'], $result['expires_in']),
                'Token refreshed successfully'
            );
        } catch (\Exception $e) {
            return $this->responseService->serverError('Token refresh failed: ' . $e->getMessage());
        }
    }

    /**
     * Get current user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $this->authService->getCurrentUser();
            
            if (!$user) {
                return $this->responseService->unauthorized('User not authenticated');
            }
            
            return $this->responseService->success($user, 'User profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to retrieve user profile: ' . $e->getMessage());
        }
    }

    /**
     * Forgot password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $success = $this->authService->forgotPassword($request->email);
            
            if ($success) {
                return $this->responseService->success(null, 'Password reset email sent successfully');
            }
            
            return $this->responseService->notFound('User not found');
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->responseService->serverError('Failed to send reset email: ' . $e->getMessage());
        }
    }

    /**
     * Reset password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'token' => 'required|string',
                'password' => 'required|string|min:8|confirmed'
            ]);

            $success = $this->authService->resetPassword($request->token, $request->password);
            
            if ($success) {
                return $this->responseService->success(null, 'Password reset successfully');
            }
            
            return $this->responseService->serverError('Password reset failed');
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->responseService->serverError('Password reset failed: ' . $e->getMessage());
        }
    }
}