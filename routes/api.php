<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserPermissionController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UnitController;



Route::get('/login', function () {
    return response()->json([
        'message' => 'Unauthorized',
        'error' => 'Authentication required.'
    ], 401);
})->name('login');

// Route for user login (generates a token)
Route::post('/login', [AuthController::class, 'login']);


Route::get('/deploy/fix', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');


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

    Route::put('/users/{id}', [UserController::class, 'update'])->middleware('permission:users.manage-permissions');


    Route::get('/users/{user}/permissions', [UserPermissionController::class, 'index'])->middleware('permission:users.view');

    Route::post('/users/{user}/permissions', [UserPermissionController::class, 'sync'])->middleware('permission:users.manage-permissions');


    // Unit Management Routes
    Route::get('units/search', [UnitController::class, 'index']); // If you want to keep a dedicated search endpoint
    Route::get('units', [UnitController::class, 'index']);
    Route::get('units/{unit}', [UnitController::class, 'show'])->middleware('permission:unit.view');
    Route::resource('units', UnitController::class)->except(['index', 'show']);
});
