<?php

namespace App\Traits;

use Tymon\JWTAuth\Facades\JWTAuth;

trait HasApiTokens
{
    /**
     * Generate JWT token for user
     */
    public function generateToken(): string
    {
        return JWTAuth::fromUser($this);
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
