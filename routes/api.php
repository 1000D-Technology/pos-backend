<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserPermissionController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UnitController; 


// Public routes
Route::get('/login', function () {
    return response()->json([
        'message' => 'Unauthorized',
        'error' => 'Authentication required.'
    ], 401);
})->name('login');

Route::post('/login', [AuthController::class, 'login']);

Route::get('/deploy/fix', function () {
    // Run all the common post-deploy commands
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');
    Artisan::call('db:seed');

    return response()->json([
        'status' => 'success',
        'message' => 'All artisan commands executed!'
    ]);
});


// Group all routes that require a valid token
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', fn(Request $request) => $request->user());

    // User Management Routes
    // These routes will require both 'auth:sanctum' (from the group) and specific permissions
    Route::get('/users', [UserController::class, 'index'])->middleware('permission:users.view');
    Route::get('/users/{id}', [UserController::class, 'show'])->middleware('permission:users.view');
    Route::put('/users/{id}', [UserController::class, 'update'])->middleware('permission:users.manage-permissions');

    Route::get('/users/{user}/permissions', [UserPermissionController::class, 'index'])->middleware('permission:users.view');
    Route::post('/users/{user}/permissions', [UserPermissionController::class, 'sync'])->middleware('permission:users.manage-permissions');

    // Unit routes
    // These routes are inside 'auth:sanctum' group, so a token is always required.
    // Specific 'permission:unit.manage' middleware is applied only to store, update, destroy.

    Route::get('units', [UnitController::class, 'index']); // Requires auth:sanctum
    Route::get('units/{unit}', [UnitController::class, 'show']); // Requires auth:sanctum
    Route::get('units/search', [UnitController::class, 'search']); // Requires auth:sanctum

    // store, update, destroy methods from apiResource will require auth:sanctum AND permission:unit.manage
    Route::apiResource('units', UnitController::class)
        ->except(['index', 'show']) // index and show are already defined above
        ->middleware('permission:unit.manage');
});