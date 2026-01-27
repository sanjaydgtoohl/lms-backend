<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Http\Controllers\Api\AuthController;

// Handle CORS preflight requests
$router->options('{any:.*}', function () {
    return response('', 200);
});

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
use App\Http\Controllers\MissCampaignController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\BriefStatusController;
use App\Http\Controllers\BriefController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\PlannerController;
use App\Http\Controllers\PlannerHistoryController;
use App\Http\Controllers\PlannerStatusController;
use App\Http\Controllers\ActivityLogController;

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
        $router->get('list', 'Api\UserController@list');
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
        $router->get('list', 'IndustryController@list');
        $router->post('/', 'IndustryController@store');
        $router->get('{id}', 'IndustryController@show');
        $router->put('{id}', 'IndustryController@update');
        $router->delete('{id}', 'IndustryController@destroy');
    });

    $router->group(['prefix' => 'designations'], function () use ($router) {
        $router->get('/', 'DesignationController@index');
        $router->get('list', 'DesignationController@list');
        $router->post('/', 'DesignationController@store');
        $router->get('{id}', 'DesignationController@show');
        $router->put('{id}', 'DesignationController@update');
        $router->delete('{id}', 'DesignationController@destroy');
    });

    $router->group(['prefix' => 'departments'], function () use ($router) {
        $router->get('/', 'DepartmentController@index');
        $router->get('list', 'DepartmentController@list');
        $router->post('/', 'DepartmentController@store');
        $router->get('{id}', 'DepartmentController@show');
        $router->put('{id}', 'DepartmentController@update');
        $router->delete('{id}', 'DepartmentController@destroy');
    });

    $router->group(['prefix' => 'lead-sources'], function () use ($router) {
        $router->get('/', 'LeadSourceController@index');
        $router->get('list', 'LeadSourceController@list');
        $router->post('/', 'LeadSourceController@store');
        $router->get('{id}', 'LeadSourceController@show');
        $router->put('{id}', 'LeadSourceController@update');
        $router->delete('{id}', 'LeadSourceController@destroy');
    });

    $router->group(['prefix' => 'lead-sub-sources'], function () use ($router) {
        $router->get('/', 'LeadSubSourceController@index');
        $router->get('list', 'LeadSubSourceController@list');
        $router->get('by-source/{sourceId}', 'LeadSubSourceController@getBySourceId');
        $router->post('/', 'LeadSubSourceController@store');
        $router->get('{id}', 'LeadSubSourceController@show');
        $router->put('{id}', 'LeadSubSourceController@update');
        $router->delete('{id}', 'LeadSubSourceController@destroy');
    });

    $router->group(['prefix' => 'countries'], function () use ($router) {
        $router->get('/', 'CountryController@index');
        $router->get('list', 'CountryController@list');
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
        $router->get('list', 'AgencyController@list');
        $router->get('create-data', 'AgencyController@create');
        $router->post('batch', 'AgencyController@storeBatch');
        $router->post('/', 'AgencyController@store');
        // Get brands for a specific agency
        $router->get('{id:[0-9]+}/brands', 'AgencyController@getBrands');

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
        $router->get('/{id:[0-9]+}/agencies', 'BrandController@agencies');
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
        $router->get('list', 'StateController@list');
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
        $router->get('list', 'CityController@list');
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
        $router->get('list', 'RoleController@list');
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
        $router->get('list', 'PermissionController@list');
        $router->get('sidebar', 'PermissionController@sidebar');
        $router->get('all-permission-tree', 'PermissionController@allPermissionTree');
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
        $router->get('{id:[0-9]+}/priorities', 'CallStatusController@getPriorities');
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
        
        // Get call statuses for a specific priority
        $router->get('{id:[0-9]+}/call-statuses', 'PriorityController@getCallStatuses');
        
        // Get lead count for a specific priority
        $router->get('{id:[0-9]+}/lead-count', 'PriorityController@getLeadCount');
        
        // Get brief count for a specific priority
        $router->get('{id:[0-9]+}/brief-count', 'PriorityController@getBriefCount');
    });

    // Lead routes
    $router->group(['prefix' => 'leads'], function () use ($router) {
        // List and filter routes first (specific routes before generic {id})
        $router->get('list', 'LeadController@list');
        $router->get('latest/two-leads', 'LeadController@latestTwo');
        $router->get('latest/follow-up-two', 'LeadController@latestTwoFollowUp');
        $router->get('latest/meeting-scheduled-two', 'LeadController@latestTwoMeetingScheduled');
        $router->get('filter', 'LeadController@filter');
        $router->get('pending', 'LeadController@pendingLeads');
        $router->get('activity-leads', 'LeadController@activity');
        $router->get('contact-persons/by-brand/{brandId:[0-9]+}', 'LeadController@getContactPersonsByBrand');
        $router->get('contact-persons/by-agency/{agencyId:[0-9]+}', 'LeadController@getContactPersonsByAgency');
        
        // Generic CRUD operations
        $router->get('/', 'LeadController@index');
        $router->post('/', 'LeadController@store');
        $router->get('{id:[0-9]+}', 'LeadController@show');
        $router->put('{id:[0-9]+}', 'LeadController@update');
        $router->patch('{id:[0-9]+}', 'LeadController@update');
        $router->delete('{id:[0-9]+}', 'LeadController@destroy');
        
        // Additional Lead routes (specific routes after generic CRUD)
        $router->get('{id:[0-9]+}/history', 'LeadController@getHistory');
        $router->post('{id:[0-9]+}/assign', 'LeadController@assign');
        $router->put('{id:[0-9]+}/assign-user', 'LeadController@updateAssignedUser');
        $router->post('{id:[0-9]+}/priority', 'LeadController@updatePriority');
        $router->post('{id:[0-9]+}/status', 'LeadController@updateStatus');
        $router->put('{id:[0-9]+}/call-status', 'LeadController@addCallStatus');
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

    // Brief Status routes
    $router->group(['prefix' => 'brief-statuses'], function () use ($router) {
        $router->get('/', 'BriefStatusController@index');
        $router->post('/', 'BriefStatusController@store');
        $router->get('priorities', 'BriefStatusController@getPrioritiesByBriefStatus');
        $router->get('by-priority', 'BriefStatusController@getBriefStatusesByPriority');
        $router->get('{id:[0-9]+}', 'BriefStatusController@show');
        $router->put('{id:[0-9]+}', 'BriefStatusController@update');
        $router->patch('{id:[0-9]+}', 'BriefStatusController@update');
        $router->delete('{id:[0-9]+}', 'BriefStatusController@destroy');
    });

    // Brief routes
    $router->group(['prefix' => 'briefs'], function () use ($router) {
        // List and filter routes first (specific routes before generic {id})
        $router->get('latest/two-briefs', 'BriefController@getLatestTwo');
        $router->get('recent', 'BriefController@getRecentBriefs');
        $router->get('list', 'BriefController@index');
        $router->get('filter', 'BriefController@index');
        $router->get('latest/five', 'BriefController@getLatestFive');
        $router->get('planner-dashboard-card', 'BriefController@getPlannerDashboardCardData');
        $router->get('brief-logs', 'BriefController@getBriefLogs');
        

        // Generic CRUD operations
        $router->get('/', 'BriefController@index');
        $router->post('/', 'BriefController@store');
        $router->get('{id:[0-9]+}', 'BriefController@show');
        $router->put('{id:[0-9]+}', 'BriefController@update');
        $router->patch('{id:[0-9]+}', 'BriefController@update');
        $router->delete('{id:[0-9]+}', 'BriefController@destroy');
        
        // Additional Brief routes
        $router->put('{id:[0-9]+}/update-status', 'BriefController@updateStatus');
        $router->put('{id:[0-9]+}/update-assign-user', 'BriefController@updateAssignUser');
        $router->get('{briefId:[0-9]+}/assign-histories', 'BriefAssignHistoryController@getByBriefId');
        $router->get('brand/{brandId:[0-9]+}', 'BriefController@getByBrand');
        $router->get('agency/{agencyId:[0-9]+}', 'BriefController@getByAgency');
        $router->get('user/{userId:[0-9]+}', 'BriefController@getByAssignedUser');
    });

    // // Brief Assign Histories by Brief
    // $router->group(['prefix' => 'briefs'], function () use ($router) {
    //     $router->get('{briefId:[0-9]+}/assign-histories', 'Api\BriefAssignHistoryController@getByBriefId');
    // });

    // // Brief Assign Histories by User
    // $router->group(['prefix' => 'users'], function () use ($router) {
    //     $router->get('{userId:[0-9]+}/assigned-briefs', 'Api\BriefAssignHistoryController@getByAssignBy');
    //     $router->get('{userId:[0-9]+}/assigned-to-me', 'Api\BriefAssignHistoryController@getByAssignTo');
    // });

    // Brief Assign Histories CRUD routes
    $router->group(['prefix' => 'brief-assign-histories'], function () use ($router) {
        $router->get('/', 'BriefAssignHistoryController@index');
        // $router->post('/', 'BriefAssignHistoryController@store');
        // $router->get('{id:[0-9]+}', 'BriefAssignHistoryController@show');
        // $router->put('{id:[0-9]+}', 'BriefAssignHistoryController@update');
        // $router->patch('{id:[0-9]+}', 'BriefAssignHistoryController@patch');
        // $router->delete('{id:[0-9]+}', 'BriefAssignHistoryController@destroy');
    });

    // Meetings routes
    $router->group(['prefix' => 'meetings'], function () use ($router) {
        $router->get('/', 'MeetingController@index');
        $router->get('list', 'MeetingController@list');
        $router->get('all', 'MeetingController@getAll');
        $router->post('/', 'MeetingController@store');
        $router->get('{id:[0-9]+}', 'MeetingController@show');
        $router->put('{id:[0-9]+}', 'MeetingController@update');
        $router->patch('{id:[0-9]+}', 'MeetingController@update');
        $router->delete('{id:[0-9]+}', 'MeetingController@destroy');
        $router->patch('{id:[0-9]+}/restore', 'MeetingController@restore');
    });

    // Meetings by lead (e.g., /api/v1/leads/1/meetings)
    $router->group(['prefix' => 'leads'], function () use ($router) {
        $router->get('{leadId:[0-9]+}/meetings', 'MeetingController@getMeetingsByLead');
    });

    // Meetings by attendee (e.g., /api/v1/users/1/meetings)
    $router->group(['prefix' => 'users'], function () use ($router) {
        $router->get('{attendeeId:[0-9]+}/meetings', 'MeetingController@getMeetingsByAttendee');
    });

    // Planners routes
    $router->group(['prefix' => 'planners'], function () use ($router) {
        // List and filter routes first (specific routes before generic {id})
        $router->get('/', 'PlannerController@index');
        $router->post('/', 'PlannerController@store');
        
        // Additional Planner routes (specific routes BEFORE generic CRUD)
        $router->post('{id:[0-9]+}/upload-submitted-plans', 'PlannerController@uploadSubmittedPlans');
        $router->post('{id:[0-9]+}/upload-backup-plan', 'PlannerController@uploadBackupPlan');
        $router->put('{id:[0-9]+}/update-status', 'PlannerController@updateStatus');
        
        // Generic CRUD operations
        $router->get('{id:[0-9]+}', 'PlannerController@show');
        $router->put('{id:[0-9]+}', 'PlannerController@update');
        $router->patch('{id:[0-9]+}', 'PlannerController@update');
        $router->delete('{id:[0-9]+}', 'PlannerController@destroy');
    });

    // Planners by brief (e.g., /api/v1/briefs/1/planners)
    $router->group(['prefix' => 'briefs'], function () use ($router) {
        $router->post('{briefId:[0-9]+}/planners', 'PlannerController@createForBrief');
        $router->get('{briefId:[0-9]+}/planners', 'PlannerController@getPlannersByBrief');
        $router->get('{briefId:[0-9]+}/planners/{id:[0-9]+}', 'PlannerController@showForBrief');
        $router->put('{briefId:[0-9]+}/planners/{id:[0-9]+}', 'PlannerController@updateForBrief');
        $router->patch('{briefId:[0-9]+}/planners/{id:[0-9]+}', 'PlannerController@updateForBrief');
        $router->delete('{briefId:[0-9]+}/planners/{id:[0-9]+}', 'PlannerController@destroyForBrief');
    });

    // Planner Histories routes
    $router->group(['prefix' => 'planner-histories'], function () use ($router) {
        // List and filter routes
        $router->get('/', 'PlannerHistoryController@index');
        $router->get('recent', 'PlannerHistoryController@getRecentHistories');
        
        // Get histories for a specific planner
        $router->get('planner/{plannerId:[0-9]+}', 'PlannerHistoryController@getPlannerHistories');
        
        // Get histories by status
        $router->get('status/{status}', 'PlannerHistoryController@getByStatus');
    });

    // Planner Statuses routes
    $router->group(['prefix' => 'planner-statuses'], function () use ($router) {
        // List and filter routes first (specific routes before generic {id})
        $router->get('/', 'PlannerStatusController@index');
        $router->post('/', 'PlannerStatusController@store');
        
        // Generic CRUD operations
        // $router->get('{id:[0-9]+}', 'PlannerStatusController@show');
        // $router->put('{id:[0-9]+}', 'PlannerStatusController@update');
        // $router->patch('{id:[0-9]+}', 'PlannerStatusController@update');
        // $router->delete('{id:[0-9]+}', 'PlannerStatusController@destroy');
    });

    // Planner Histories by brief (e.g., /api/v1/briefs/1/planner-histories)
    $router->group(['prefix' => 'briefs'], function () use ($router) {
        $router->get('{briefId:[0-9]+}/planner-histories', 'PlannerHistoryController@getBriefPlannerHistories');
    });

    // Super Admin Dashboard routes
    $router->group(['prefix' => 'super-admin-dashboard'], function () use ($router) {
        $router->get('/', 'Api\DashboardController@getDashboard');
    });

    // Activity Log routes
    $router->group(['prefix' => 'activity-logs'], function () use ($router) {
        $router->get('/', 'ActivityLogController@index');
        $router->get('recent', 'ActivityLogController@getRecentActivities');
        $router->get('by-action/{action}', 'ActivityLogController@getActivityLogsByAction');
        $router->get('model/{model}/{modelId:[0-9]+}', 'ActivityLogController@getModelActivityLogs');
        $router->get('user/{userId:[0-9]+}', 'ActivityLogController@getUserActivityLogs');
        $router->get('{id:[0-9]+}', 'ActivityLogController@show');
        $router->delete('old-logs', 'ActivityLogController@deleteOldActivityLogs');
        $router->delete('{id:[0-9]+}', 'ActivityLogController@destroy');
    });
});

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