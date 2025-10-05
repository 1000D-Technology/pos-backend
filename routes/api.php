<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\UserPermissionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;


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

    Route::get('/users', [UserController::class, 'index'])->middleware('permission:users.view');
    Route::get('/users/{id}', [UserController::class, 'show'])->middleware('permission:users.view');
    Route::put('/users/{id}', [UserController::class, 'update'])->middleware('permission:users.manage-permissions');

    Route::get('/users/{user}/permissions', [UserPermissionController::class, 'index'])->middleware('permission:users.view');
    Route::post('/users/{user}/permissions', [UserPermissionController::class, 'sync'])->middleware('permission:users.manage-permissions');



    // --- POS Product Routes ---
    Route::get('/products', function () {
        return response()->json(['message' => 'Viewing all products.'], status: 200);
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


    // Protected Category Routes (Create, Update, Delete, Restore, Bulk Operations)
    // Note: Order matters - specific routes must come before parameterized routes
    Route::get('/categories/deleted', [CategoryController::class, 'deleted'])
        ->middleware('permission:categories.manage');

    Route::post('/categories/bulk-delete', [CategoryController::class, 'bulkDelete'])
        ->middleware('permission:categories.manage');

    Route::post('/categories/bulk-restore', [CategoryController::class, 'bulkRestore'])
        ->middleware('permission:categories.manage');

    Route::post('/categories/{id}/restore', [CategoryController::class, 'restore'])
        ->middleware('permission:categories.manage');
    //supplier routes

    //search by name
    Route::get('/suppliers/search', [SupplierController::class, 'search'])->middleware('permission:suppliers.view');
    //search all suppliers
    Route::get('/suppliers', [SupplierController::class, 'index'])->middleware('permission:suppliers.view');
    //create a new supplier
    Route::post('/suppliers', [SupplierController::class, 'store'])->middleware('permission:suppliers.create');
    //view a single supplier by id
    Route::get('/suppliers/{id}', [SupplierController::class, 'show'])->middleware('permission:suppliers.view');
    //update a supplier
    Route::put('/suppliers/{id}', [SupplierController::class, 'update'])->middleware('permission:suppliers.update');
    //supplier delete
    Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])->middleware('permission:suppliers.delete');

    // Bank routes
    Route::get('/banks', [BankController::class, 'index'])->middleware('permission:bank.view');
    Route::post('/banks', [BankController::class, 'store'])->middleware('permission:bank.manage-permissions');
    Route::get('/banks/{id}', [BankController::class, 'show'])->middleware('permission:bank.view');
    Route::put('/banks/{id}', [BankController::class, 'update'])->middleware('permission:bank.manage-permissions');
    Route::delete('/banks/{id}', [BankController::class, 'destroy'])->middleware('permission:bank.manage-permissions');
    // Public Category Routes
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::get('/categories/search/query', [CategoryController::class, 'search']);

    Route::apiResource('categories', CategoryController::class)
        ->except(['index', 'show'])
        ->middleware('permission:categories.manage');

    // Customer Routes - Protected with specific permissions
    Route::get('/customers/deleted', [CustomerController::class, 'deleted'])
        ->middleware('permission:customers.view');

    Route::patch('/customers/{id}/restore', [CustomerController::class, 'restore'])
        ->middleware('permission:customers.restore');

    Route::get('/customers/search', [CustomerController::class, 'search'])
        ->middleware('permission:customers.search');

    Route::get('/customers', [CustomerController::class, 'index'])
        ->middleware('permission:customers.view');

    Route::post('/customers', [CustomerController::class, 'store'])
        ->middleware('permission:customers.create');

    Route::get('/customers/{id}', [CustomerController::class, 'show'])
        ->middleware('permission:customers.view');

    Route::put('/customers/{id}', [CustomerController::class, 'update'])
        ->middleware('permission:customers.update');

    Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])
        ->middleware('permission:customers.delete');
});

// Public Category Routes - These don't require authentication
// Note: Search must come before {id} route to avoid route conflicts
Route::get('/categories/search', [CategoryController::class, 'search']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
