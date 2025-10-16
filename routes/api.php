<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });
});

// Protected routes (authentication required)
Route::prefix('v1')->middleware(['jwt.auth'])->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // User routes
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware('permission:users:read');
        Route::get('search', [UserController::class, 'search'])->middleware('permission:users:read');
        Route::get('statistics', [UserController::class, 'statistics'])->middleware('permission:users:read');
        Route::get('{id}', [UserController::class, 'show'])->middleware('permission:users:read');
        Route::post('/', [UserController::class, 'store'])->middleware('permission:users:create');
        Route::put('{id}', [UserController::class, 'update'])->middleware('permission:users:update');
        Route::delete('{id}', [UserController::class, 'destroy'])->middleware('permission:users:delete');
        Route::post('{id}/change-password', [UserController::class, 'changePassword'])->middleware('permission:users:update');
    });

    // Profile routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [UserController::class, 'me'])->middleware('permission:profile:read');
        Route::put('/', [UserController::class, 'updateProfile'])->middleware('permission:profile:update');
    });
});

// Admin routes (admin role required)
Route::prefix('v1/admin')->middleware(['jwt.auth', 'role:admin'])->group(function () {
    // Admin specific routes can be added here
    Route::get('dashboard', function () {
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

// Fallback route for undefined API endpoints
Route::fallback(function () {
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
