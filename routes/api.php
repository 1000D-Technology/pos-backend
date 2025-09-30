<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserPermissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Public route
Route::post('/login', [AuthController::class, 'login']);

// Group all routes that require a valid token
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', fn(Request $request) => $request->user());

    // --- POS Product Routes ---
    Route::get('/products', function () {
        return response()->json(['message' => 'Viewing all products.'],status: 200);
    })->middleware('permission:products.view');
    
    // Protected routes using the 'permission' middleware
    Route::post('/products', function () {
        return response()->json(['message' => 'Product created!'], 201);
    })->middleware('permission:products.create');
    
    Route::put('/products/{id}', function ($id) {
        return response()->json(['message' => "Product {$id} updated!"]);
    })->middleware('permission:products.update');

    Route::delete('/products/{id}', function ($id) {
        return response()->json(['message' => "Product {$id} deleted!"]);
    })->middleware('permission:products.delete');

    Route::post('/users/{user}/permissions', [UserPermissionController::class, 'sync'])
        ->middleware('permission:users.manage-permissions');

        
});