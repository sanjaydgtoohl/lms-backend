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
use App\Http\Controllers\CountryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BrandTypeController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\BrandAgencyRelationshipController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\CallStatusController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\PriorityController;

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

    $router->group(['prefix' => 'countries'], function () use ($router) {
        $router->get('/', 'CountryController@index');
        $router->get('{id:[0-9]+}', 'CountryController@show');
        $router->get('{id:[0-9]+}/states', 'StateController@getStatesByCountry');
        $router->get('{id:[0-9]+}/cities', 'CityController@getCitiesByCountry');
    });
    
    $router->group(['prefix' => 'agency-groups'], function () use ($router) {
        $router->get('/', 'AgencyGroupController@index');
        $router->post('/', 'AgencyGroupController@store');
        $router->get('{id:[0-9]+}', 'AgencyGroupController@show');
        $router->put('{id:[0-9]+}', 'AgencyGroupController@update');
        $router->patch('{id:[0-9]+}', 'AgencyGroupController@update');
        $router->delete('{id:[0-9]+}', 'AgencyGroupController@destroy');
    });
    
    // Agency Type routes
    $router->group(['prefix' => 'agency-types'], function () use ($router) {
        $router->get('/', 'AgencyTypeController@index');
        $router->post('/', 'AgencyTypeController@store');
        $router->get('/{id:[0-9]+}', 'AgencyTypeController@show');
        $router->put('/{id:[0-9]+}', 'AgencyTypeController@update');
        $router->patch('/{id:[0-9]+}', 'AgencyTypeController@update');
        $router->delete('/{id:[0-9]+}', 'AgencyTypeController@destroy');
    });
    
    $router->group(['prefix' => 'agencies'], function () use ($router) {
        $router->get('/', 'AgencyController@index');
        $router->get('create-data', 'AgencyController@create');
        $router->post('batch', 'AgencyController@storeBatch');
        $router->post('/', 'AgencyController@store');
        $router->get('{id:[0-9]+}', 'AgencyController@show');
        $router->put('{id:[0-9]+}', 'AgencyController@update');
        $router->patch('{id:[0-9]+}', 'AgencyController@update');
        $router->delete('{id:[0-9]+}', 'AgencyController@destroy');
    });
    // Brand routes
    $router->group(['prefix' => 'brands'], function () use ($router) {
        $router->get('/', 'BrandController@index');
        $router->post('/', 'BrandController@store');
        $router->get('/list', 'BrandController@list');
        $router->get('/{id:[0-9]+}', 'BrandController@show');      
        $router->put('/{id:[0-9]+}', 'BrandController@update');     
        $router->patch('/{id:[0-9]+}', 'BrandController@update'); 
        $router->delete('/{id:[0-9]+}', 'BrandController@destroy');
    });
    // --- Brand Type Routes ---
    $router->group(['prefix' => 'brand-types'], function () use ($router) {
        $router->get('/', 'BrandTypeController@index');
        $router->post('/', 'BrandTypeController@store');
        $router->get('/{id:[0-9]+}', 'BrandTypeController@show');
        $router->put('/{id:[0-9]+}', 'BrandTypeController@update');
        $router->patch('/{id:[0-9]+}', 'BrandTypeController@update');
        $router->delete('/{id:[0-9]+}', 'BrandTypeController@destroy');
    });
    $router->group(['prefix' => 'regions'], function () use ($router) {
        $router->get('/', 'RegionController@index');
        $router->post('/', 'RegionController@store');
        $router->get('/{id:[0-9]+}', 'RegionController@show');
        $router->put('/{id:[0-9]+}', 'RegionController@update');
        $router->patch('/{id:[0-9]+}', 'RegionController@update');
        $router->delete('/{id:[0-9]+}', 'RegionController@destroy');
    });

    // --- BRAND AGENCY RELATIONSHIP ROUTES (NEW) ---
    $router->group(['prefix' => 'brand-agency-relationships'], function () use ($router) {
        $router->get('/', 'BrandAgencyRelationshipController@index');
        $router->post('/', 'BrandAgencyRelationshipController@store'); // Attach/Create
        $router->get('{id:[0-9]+}', 'BrandAgencyRelationshipController@show');
        // We explicitly skip PUT/PATCH as update is not meaningful for simple pivot tables
        $router->delete('{id:[0-9]+}', 'BrandAgencyRelationshipController@destroy'); // Detach/Delete
    });

    // States routes
    $router->group(['prefix' => 'states'], function () use ($router) {
        $router->get('/', 'StateController@index');
        $router->get('all', 'StateController@getAll');
        $router->post('/', 'StateController@store');
        $router->get('{id:[0-9]+}', 'StateController@show');
        $router->put('{id:[0-9]+}', 'StateController@update');
        $router->patch('{id:[0-9]+}', 'StateController@update');
        $router->delete('{id:[0-9]+}', 'StateController@destroy');
        $router->get('{id:[0-9]+}/cities', 'CityController@getCitiesByState');
    });

    // Cities routes
    $router->group(['prefix' => 'cities'], function () use ($router) {
        $router->get('/', 'CityController@index');
        $router->get('all', 'CityController@getAll');
        $router->post('/', 'CityController@store');
        $router->get('{id:[0-9]+}', 'CityController@show');
        $router->put('{id:[0-9]+}', 'CityController@update');
        $router->patch('{id:[0-9]+}', 'CityController@update');
        $router->delete('{id:[0-9]+}', 'CityController@destroy');
    });

    $router->group(['prefix' => 'zones'], function () use ($router) {
        $router->get('/', 'ZoneController@index');
        $router->get('all', 'ZoneController@getAll');
        $router->post('/', 'ZoneController@store');
        $router->get('{id:[0-9]+}', 'ZoneController@show');
        $router->put('{id:[0-9]+}', 'ZoneController@update');
        $router->patch('{id:[0-9]+}', 'ZoneController@update');
        $router->delete('{id:[0-9]+}', 'ZoneController@destroy');
    });

    // Roles routes
    $router->group(['prefix' => 'roles'], function () use ($router) {
        $router->get('/', 'RoleController@index');
        $router->post('/', 'RoleController@store');
        $router->get('{id:[0-9]+}', 'RoleController@show');
        $router->put('{id:[0-9]+}', 'RoleController@update');
        $router->patch('{id:[0-9]+}', 'RoleController@update');
        $router->delete('{id:[0-9]+}', 'RoleController@destroy');

        // Permissions management for a role
        $router->post('{id:[0-9]+}/permissions', 'RoleController@syncPermissions');
        $router->post('{id:[0-9]+}/permissions/attach', 'RoleController@attachPermission');
        $router->post('{id:[0-9]+}/permissions/detach', 'RoleController@detachPermission');
    });

    // Permissions routes
    $router->group(['prefix' => 'permissions'], function () use ($router) {
        $router->get('/', 'PermissionController@index');
        $router->post('/', 'PermissionController@store');
        $router->get('{id:[0-9]+}', 'PermissionController@show');
        $router->put('{id:[0-9]+}', 'PermissionController@update');
        $router->patch('{id:[0-9]+}', 'PermissionController@update');
        $router->delete('{id:[0-9]+}', 'PermissionController@destroy');
    });

    

    // Call Status routes
    $router->group(['prefix' => 'call-statuses'], function () use ($router) {
        $router->get('/', 'CallStatusController@index');
        //$router->post('/', 'CallStatusController@store');
        $router->get('{id:[0-9]+}', 'CallStatusController@show');
        //$router->put('{id:[0-9]+}', 'CallStatusController@update');
        //$router->patch('{id:[0-9]+}', 'CallStatusController@update');
        //$router->delete('{id:[0-9]+}', 'CallStatusController@destroy');
    });

    // Status routes
    $router->group(['prefix' => 'statuses'], function () use ($router) {
        $router->get('/', 'StatusController@index');
        //$router->post('/', 'StatusController@store');
        $router->get('{id:[0-9]+}', 'StatusController@show');
        //$router->put('{id:[0-9]+}', 'StatusController@update');
        //$router->patch('{id:[0-9]+}', 'StatusController@update');
        //$router->delete('{id:[0-9]+}', 'StatusController@destroy');
    });

    // Priority routes
    $router->group(['prefix' => 'priorities'], function () use ($router) {
        $router->get('/', 'PriorityController@index');
        $router->post('/', 'PriorityController@store');
        $router->get('{id:[0-9]+}', 'PriorityController@show');
        $router->put('{id:[0-9]+}', 'PriorityController@update');
        $router->patch('{id:[0-9]+}', 'PriorityController@update');
        $router->delete('{id:[0-9]+}', 'PriorityController@destroy');
    });

    // Lead routes
    $router->group(['prefix' => 'leads'], function () use ($router) {
        // List and filter routes first (specific routes before generic {id})
        $router->get('/list', 'LeadController@list');
        $router->get('/filter', 'LeadController@filter');
        
        // Generic CRUD operations
        $router->get('/', 'LeadController@index');
        $router->post('/', 'LeadController@store');
        $router->get('{id:[0-9]+}', 'LeadController@show');
        $router->put('{id:[0-9]+}', 'LeadController@update');
        $router->patch('{id:[0-9]+}', 'LeadController@update');
        $router->delete('{id:[0-9]+}', 'LeadController@destroy');
        
        // Additional Lead routes (specific routes after generic CRUD)
        $router->post('{id:[0-9]+}/assign', 'LeadController@assign');
        $router->post('{id:[0-9]+}/priority', 'LeadController@updatePriority');
        $router->post('{id:[0-9]+}/status', 'LeadController@updateStatus');
        $router->post('{id:[0-9]+}/call-status', 'LeadController@addCallStatus');
        $router->delete('{id:[0-9]+}/call-status/{callStatusId:[0-9]+}', 'LeadController@removeCallStatus');
    });

    // Miss Campaign routes
    $router->group(['prefix' => 'miss-campaigns'], function () use ($router) {
        $router->get('/', 'MissCampaignController@index');
        $router->post('/', 'MissCampaignController@store');
        $router->get('/list', 'MissCampaignController@list');
        $router->get('/{id:[0-9]+}', 'MissCampaignController@show');      
        $router->put('/{id:[0-9]+}', 'MissCampaignController@update');     
        $router->patch('/{id:[0-9]+}', 'MissCampaignController@update'); 
        $router->delete('/{id:[0-9]+}', 'MissCampaignController@destroy');
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