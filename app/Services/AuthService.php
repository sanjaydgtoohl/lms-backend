<?php

namespace App\Services;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthService
{
    /**
     * The user service instance
     *
     * @var UserService
     */
    protected $userService;

    /**
     * Constructor
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Register a new user
     *
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function register(array $data): array
    {
        $this->validateRegistrationData($data);

        // Create user
        $user = $this->userService->createUser($data);

        // Generate token
        $token = $user->generateToken();

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ];
    }

    /**
     * Login user
     *
     * @param array $credentials
     * @return array
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        $this->validateLoginData($credentials);

        $user = $this->userService->authenticateUser($credentials);

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        // Generate token
        $token = $user->generateToken();

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ];
    }

    /**
     * Logout user
     *
     * @return bool
     */
    public function logout(): bool
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return true;
        } catch (JWTException $e) {
            return false;
        }
    }

    /**
     * Refresh token
     *
     * @return array
     * @throws JWTException
     */
    public function refresh(): array
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());
        $user = JWTAuth::parseToken()->authenticate();

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ];
    }

    /**
     * Get current authenticated user
     *
     * @return User|null
     */
    public function getCurrentUser(): ?User
    {
        try {
            return JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return null;
        }
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        try {
            JWTAuth::parseToken()->authenticate();
            return true;
        } catch (JWTException $e) {
            return false;
        }
    }

    /**
     * Forgot password
     *
     * @param string $email
     * @return bool
     */
    public function forgotPassword(string $email): bool
    {
        // This would typically send an email with reset link
        // For now, we'll just return true if user exists
        $user = $this->userService->getUserById($email);
        return $user !== null;
    }

    /**
     * Reset password
     *
     * @param string $token
     * @param string $password
     * @return bool
     * @throws ValidationException
     */
    public function resetPassword(string $token, string $password): bool
    {
        // This would typically validate the reset token
        // For now, we'll just validate the password format
        $validator = Validator::make(['password' => $password], [
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // In a real implementation, you would:
        // 1. Validate the reset token
        // 2. Find the user associated with the token
        // 3. Update their password
        // 4. Invalidate the token

        return true;
    }

    /**
     * Validate registration data
     *
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    protected function validateRegistrationData(array $data): void
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validate login data
     *
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    protected function validateLoginData(array $data): void
    {
        $validator = Validator::make($data, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
