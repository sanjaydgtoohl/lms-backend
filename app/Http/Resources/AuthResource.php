<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class AuthResource extends BaseResource
{
    /**
     * The user resource instance
     *
     * @var UserResource
     */
    protected $userResource;

    /**
     * The token
     *
     * @var string
     */
    protected $token;

    /**
     * The token type
     *
     * @var string
     */
    protected $tokenType;

    /**
     * The token expiration time
     *
     * @var int
     */
    protected $expiresIn;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  string  $token
     * @param  string  $tokenType
     * @param  int  $expiresIn
     * @return void
     */
    public function __construct($resource, string $token, string $tokenType = 'bearer', int $expiresIn = 3600)
    {
        parent::__construct($resource);
        $this->token = $token;
        $this->tokenType = $tokenType;
        $this->expiresIn = $expiresIn;
        $this->userResource = new UserResource($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'user' => $this->userResource->toArray($request),
            'token' => $this->token,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'timestamp' => now()->toISOString(),
                'message' => 'Authentication successful',
            ]
        ];
    }
}
