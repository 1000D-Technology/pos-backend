<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserPermissionController;
use App\Models\User; // You might not need this if not directly used in a route closure
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UnitController; // Ensure this is correctly imported


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

// Public routes - No authentication required for these
// This route will return an Unauthorized message if someone tries to access /api/login via GET.
Route::get('/login', function () {
    return response()->json([
        'message' => 'Unauthorized',
        'error' => 'Authentication required.'
    ], 401);
})->name('login');

// Route for user login (generates a token)
Route::post('/login', [AuthController::class, 'login']);

// Route to run common post-deployment artisan commands (use with caution in production)
Route::get('/deploy/fix', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');
    // Artisan::call('db:seed'); // Be cautious with db:seed on production if it truncates data

    return response()->json([
        'status' => 'success',
        'message' => 'All artisan commands executed!'
    ]);
});


// Group all routes that require a valid authentication token (Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // Authenticated user's own data
    Route::get('/user', fn(Request $request) => $request->user());

    // User Management Routes (require authentication AND specific permissions)
    // List all users
    Route::get('/users', [UserController::class, 'index'])->middleware('permission:users.view');
    // Show a specific user
    Route::get('/users/{id}', [UserController::class, 'show'])->middleware('permission:users.view');
    // Update a user (e.g., their permissions or roles)
    Route::put('/users/{id}', [UserController::class, 'update'])->middleware('permission:users.manage-permissions');

    // User Permissions Management Routes
    // List permissions for a specific user
    Route::get('/users/{user}/permissions', [UserPermissionController::class, 'index'])->middleware('permission:users.view');
    // Sync (assign/revoke) permissions for a specific user
    Route::post('/users/{user}/permissions', [UserPermissionController::class, 'sync'])->middleware('permission:users.manage-permissions');


    // Unit Management Routes
    // These routes are within the 'auth:sanctum' group, so all will require an authentication token.

    // GET /api/units - List all active units or search units by 'search' query parameter
    // Requires 'unit.view' permission in addition to authentication.
    Route::get('units', [UnitController::class, 'index'])->middleware('permission:unit.view');

    // GET /api/units/{unit} - Show a specific active unit
    // Requires 'unit.view' permission in addition to authentication.
    Route::get('units/{unit}', [UnitController::class, 'show'])->middleware('permission:unit.view');

    // apiResource for POST, PUT, DELETE operations on units.
    // These require 'unit.manage' permission in addition to authentication.
    // 'index' and 'show' methods are excluded here because they are explicitly defined above
    // with a different (or more specific) permission: 'unit.view'.
    Route::apiResource('units', UnitController::class)
        ->except(['index', 'show'])
        ->middleware('permission:unit.manage');
});