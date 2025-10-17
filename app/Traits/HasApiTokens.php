<?php

namespace App\Traits;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

trait HasApiTokens
{
    /**
     * Generate JWT token for user
     */
    public function generateToken(): string
    {
        try {
            return JWTAuth::fromUser($this); // Generate token for this user
        } catch (JWTException $e) {
            // Log the error if needed
            \Log::error('JWT token generation failed: ' . $e->getMessage());
            return $e->getMessage(); // Return null if token cannot be generated
        }
    }

    /**
     * Revoke user token
     */
    public function revokeToken(): bool
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Refresh user token
     */
    public function refreshToken(): string
    {
        return JWTAuth::refresh(JWTAuth::getToken());
    }

    /**
     * Get current user from token
     */
    public static function getCurrentUser(): ?static
    {
        try {
            return JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return null;
        }
    }
}