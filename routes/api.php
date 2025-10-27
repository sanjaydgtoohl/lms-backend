<?php

/** @var \Laravel\Lumen\Routing\Router $router */


use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\IndustryController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\LeadSourceController;
use App\Http\Controllers\LeadSubSourceController;
use App\Http\Controllers\AgencyGroupController;
use App\Http\Controllers\AgencyTypeController;
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\LocationController;
use Carbon\Carbon;

// -------------------------------------------------------
// Public routes (no authentication required)
// -------------------------------------------------------

$router->group(['prefix' => 'v1'], function () use ($router) {
    
    // Authentication routes
    $router->group(['prefix' => 'auth'], function () use ($router) {     
        $router->post('register', 'Api\AuthController@register');
        $router->post('login', 'Api\AuthController@login');
        $router->post('forgot-password','Api\AuthController@forgotPassword');
        $router->post('reset-password', 'Api\AuthController@resetPassword');
    });
});

// -------------------------------------------------------
// Protected routes (JWT authentication required)
// -------------------------------------------------------

$router->group(['prefix' => 'v1', 'middleware' => 'jwt.auth'], function () use ($router) {

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
        $router->get('login-history', 'Api\UserController@getLoginHistory');
    });

    // Industry routes
    
    $router->group(['prefix' => 'industries'], function () use ($router) {
    
        $router->get('/', 'IndustryController@index');
        $router->post('/', 'IndustryController@store');
        $router->get('{id}', 'IndustryController@show');
        $router->put('{id}', 'IndustryController@update');
        $router->delete('{id}', 'IndustryController@destroy');
    });

    $router->group(['prefix' => 'designations'], function () use ($router) {
        $router->get('/', 'DesignationController@index');
        $router->post('/', 'DesignationController@store');
        $router->get('{id}', 'DesignationController@show');
        $router->put('{id}', 'DesignationController@update');
        $router->delete('{id}', 'DesignationController@destroy');
    });

    $router->group(['prefix' => 'departments'], function () use ($router) {
        $router->get('/', 'DepartmentController@index');
        $router->post('/', 'DepartmentController@store');
        $router->get('{id}', 'DepartmentController@show');
        $router->put('{id}', 'DepartmentController@update');
        $router->delete('{id}', 'DepartmentController@destroy');
    });

    $router->group(['prefix' => 'lead-sources'], function () use ($router) {
        $router->get('/', 'LeadSourceController@index');
        $router->post('/', 'LeadSourceController@store');
        $router->get('{id}', 'LeadSourceController@show');
        $router->put('{id}', 'LeadSourceController@update');
        $router->delete('{id}', 'LeadSourceController@destroy');
    });

    $router->group(['prefix' => 'lead-sub-sources'], function () use ($router) {
        $router->get('/', 'LeadSubSourceController@index');
        $router->post('/', 'LeadSubSourceController@store');
        $router->get('{id}', 'LeadSubSourceController@show');
        $router->put('{id}', 'LeadSubSourceController@update');
        $router->delete('{id}', 'LeadSubSourceController@destroy');
    });
    //Agency Group (Simple CRUD)
    $router->group(['prefix' => 'agency-groups'], function () use ($router) {
        $router->get('/', 'AgencyGroupController@index');
        $router->post('/', 'AgencyGroupController@store');
        $router->get('{id}', 'AgencyGroupController@show');
        $router->put('{id}', 'AgencyGroupController@update');
        $router->delete('{id}', 'AgencyGroupController@destroy');
    });
    
    //Agency Type (list)
    $router->group(['prefix' => 'agency-types'], function () use ($router) {
        $router->get('/', 'AgencyTypeController@index');
    });

    //Agency (Main Complex CRUD)
    $router->group(['prefix' => 'agencies'], function () use ($router) {
        $router->get('/', 'AgencyController@index');
        $router->post('/', 'AgencyController@store'); // Complex bulk create
        $router->get('{id}', 'AgencyController@show');
        $router->put('{id}', 'AgencyController@update'); // Simple update
        $router->delete('{id}', 'AgencyController@destroy');
    });

    // Location routes
    $router->group(['prefix' => 'locations'], function () use ($router) {
        $router->get('countries', 'LocationController@getCountries');
        $router->get('states/{country_id}', 'LocationController@getStates');
        $router->get('cities/{state_id}', 'LocationController@getCities');
    });
});

// -------------------------------------------------------
// Admin routes (admin role required)
// -------------------------------------------------------

$router->group(['prefix' => 'v1/admin', 'middleware' => ['jwt.auth', 'role:admin']], function () use ($router) {
    $router->get('dashboard', function () {
        return response()->json([
            'success' => true,
            'message' => 'Admin dashboard accessed successfully',
            'data' => [
                'admin_panel' => true,
                'timestamp' => Carbon::now()->toIso8601String()
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
            'timestamp' => Carbon::now()->toIso8601String(),
            'status_code' => 404,
        ]
    ], 404);
});