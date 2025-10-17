<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

// -------------------------------------------------------
// Public routes (no authentication required)
// -------------------------------------------------------

$router->group(['prefix' => 'api/v1'], function () use ($router) {

    // Authentication routes
    $router->group(['prefix' => 'auth'], function () use ($router) {
        $router->post('register', 'Api\AuthController@register');
        $router->post('login', 'Api\AuthController@login');
        $router->post('forgot-password', 'Api\AuthController@forgotPassword');
        $router->post('reset-password', 'Api\AuthController@resetPassword');
    });
});

// -------------------------------------------------------
// Protected routes (JWT authentication required)
// -------------------------------------------------------

$router->group(['prefix' => 'api/v1', 'middleware' => 'jwt.auth'], function () use ($router) {

    // Auth routes
    $router->group(['prefix' => 'auth'], function () use ($router) {
        $router->post('logout', 'Api\AuthController@logout');
        $router->post('refresh', 'Api\AuthController@refresh');
        $router->get('me', 'Api\AuthController@me');
    });

    // User routes
    $router->group(['prefix' => 'users'], function () use ($router) {
        $router->get('/', 'Api\UserController@index');
        $router->get('search', 'Api\UserController@search');
        $router->get('statistics', 'Api\UserController@statistics');
        $router->get('{id}', 'Api\UserController@show');
        $router->post('/', 'Api\UserController@store');
        $router->put('{id}', 'Api\UserController@update');
        $router->delete('{id}', 'Api\UserController@destroy');
        $router->post('{id}/change-password', 'Api\UserController@changePassword');
    });

    // Profile routes
    $router->group(['prefix' => 'profile'], function () use ($router) {
        $router->get('/', 'Api\UserController@me');
        $router->put('/', 'Api\UserController@updateProfile');
    });
});

// -------------------------------------------------------
// Admin routes (admin role required)
// -------------------------------------------------------

$router->group(['prefix' => 'api/v1/admin', 'middleware' => ['jwt.auth', 'role:admin']], function () use ($router) {
    $router->get('dashboard', function () {
        return response()->json([
            'success' => true,
            'message' => 'Admin dashboard accessed successfully',
            'data' => [
                'admin_panel' => true,
                'timestamp' => now()->toISOString()
            ]
        ]);
    });
});

// -------------------------------------------------------
// Fallback route for undefined API endpoints
// -------------------------------------------------------

$router->get('{any:.*}', function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
        'error_code' => 'NOT_FOUND',
        'meta' => [
            'timestamp' => now()->toISOString(),
            'status_code' => 404,
        ]
    ], 404);
});