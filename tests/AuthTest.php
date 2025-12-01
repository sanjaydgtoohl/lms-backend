<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make('config')->set('database.default', 'sqlite');
        $this->app->make('config')->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $this->app->make('config')->set('jwt.secret', 'testingsecret');
        $this->app->make('config')->set('jwt.required_claims', [
            'iat','exp','nbf','sub','jti'
        ]);
        putenv('JWT_SECRET=testingsecret');
        $_ENV['JWT_SECRET'] = 'testingsecret';
        $this->artisan('migrate', ['--path' => 'database/migrations/2024_01_01_000001_create_users_table.php']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2024_01_01_000002_create_profiles_table.php']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_10_17_141736_login_logs.php']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2025_11_05_094917_laratrust_setup_tables.php']);
    }

    public function test_register_success()
    {
        $email = 'user_' . uniqid() . '@example.com';
        $payload = [
            'name' => 'Test User',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->post('/api/v1/auth/register', $payload);

        $this->seeStatusCode(201);
        $this->seeJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
                'refresh_token',
                'token_type',
                'expires_in',
            ],
            'meta' => [
                'timestamp',
                'status_code',
            ],
        ]);
    }

    public function test_register_validation_error()
    {
        $payload = [
            'name' => 'No Email User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->post('/api/v1/auth/register', $payload);

        $this->seeStatusCode(422);
        $this->seeJsonStructure([
            'success',
            'message',
            'errors',
            'error_code',
            'meta' => [
                'timestamp',
                'status_code',
            ],
        ]);
    }

    public function test_login_success()
    {
        $email = 'login_' . uniqid() . '@example.com';

        $this->post('/api/v1/auth/register', [
            'name' => 'Login User',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->post('/api/v1/auth/login', [
            'email' => $email,
            'password' => 'password123',
        ]);

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
                'refresh_token',
                'token_type',
                'expires_in',
            ],
            'meta' => [
                'timestamp',
                'status_code',
            ],
        ]);
    }

    public function test_login_invalid_credentials()
    {
        $this->post('/api/v1/auth/login', [
            'email' => 'unknown@example.com',
            'password' => 'wrongpass',
        ]);

        $this->seeStatusCode(422);
        $this->seeJsonStructure([
            'success',
            'message',
            'errors',
            'error_code',
            'meta' => [
                'timestamp',
                'status_code',
            ],
        ]);
    }
}