<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\UserPermissionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\CompanyBankAccountController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\AttendanceController;
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

// User login (generates a token)
Route::post('/login', [AuthController::class, 'login']);

Route::get('/deploy/fix', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');
    Artisan::call('l5-swagger:generate');
    Artisan::call('migrate:fresh', [
        '--seed' => true
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'All artisan commands executed!'
    ]);
});

// Routes requiring Sanctum authentication
Route::middleware('auth:sanctum')->group(function () {

    // Authenticated user info
    Route::get('/user', fn(Request $request) => $request->user());

    // User Management
    Route::get('/users', [UserController::class, 'index'])->middleware('permission:users.view');
    Route::get('/users/{id}', [UserController::class, 'show'])->middleware('permission:users.view');
    Route::put('/users/{id}', [UserController::class, 'update'])->middleware('permission:users.manage-permissions');
    Route::get('/users/{user}/permissions', [UserPermissionController::class, 'index'])->middleware('permission:users.view');
    Route::post('/users/{user}/permissions', [UserPermissionController::class, 'sync'])->middleware('permission:users.manage-permissions');

    // Unit Management
    Route::get('/units', [UnitController::class, 'index']);
    Route::post('/units', [UnitController::class, 'store'])->middleware('permission:units.create');
    Route::get('/units/{id}', [UnitController::class, 'show'])->middleware('permission:units.view');
    Route::put('/units/{id}', [UnitController::class, 'update'])->middleware('permission:units.update');
    Route::delete('/units/{id}', [UnitController::class, 'destroy'])->middleware('permission:units.delete');

    // Supplier Management
    Route::get('/suppliers/search', [SupplierController::class, 'search'])->middleware('permission:suppliers.view');
    Route::get('/suppliers', [SupplierController::class, 'index'])->middleware('permission:suppliers.view');
    Route::post('/suppliers', [SupplierController::class, 'store'])->middleware('permission:suppliers.create');
    Route::get('/suppliers/{id}', [SupplierController::class, 'show'])->middleware('permission:suppliers.view');
    Route::put('/suppliers/{id}', [SupplierController::class, 'update'])->middleware('permission:suppliers.update');
    Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])->middleware('permission:suppliers.delete');


    // Bank Routes
    Route::get('/banks', [BankController::class, 'index'])->middleware('permission:bank.view');
    Route::post('/banks', [BankController::class, 'store'])->middleware('permission:bank.manage-permissions');
    Route::get('/banks/{id}', [BankController::class, 'show'])->middleware('permission:bank.view');
    Route::put('/banks/{id}', [BankController::class, 'update'])->middleware('permission:bank.manage-permissions');
    Route::delete('/banks/{id}', [BankController::class, 'destroy'])->middleware('permission:bank.manage-permissions');

    // Category Routes (protected)
    Route::get('/categories/deleted', [CategoryController::class, 'deleted'])->middleware('permission:categories.manage');
    Route::post('/categories/bulk-delete', [CategoryController::class, 'bulkDelete'])->middleware('permission:categories.manage');
    Route::post('/categories/bulk-restore', [CategoryController::class, 'bulkRestore'])->middleware('permission:categories.manage');
    Route::post('/categories/{id}/restore', [CategoryController::class, 'restore'])->middleware('permission:categories.manage');
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show'])->middleware('permission:categories.manage');

    // Customer Routes
    Route::get('/customers/deleted', [CustomerController::class, 'deleted'])->middleware('permission:customers.view');
    Route::patch('/customers/{id}/restore', [CustomerController::class, 'restore'])->middleware('permission:customers.restore');
    Route::get('/customers/search', [CustomerController::class, 'search'])->middleware('permission:customers.search');
    Route::get('/customers', [CustomerController::class, 'index'])->middleware('permission:customers.view');
    Route::post('/customers', [CustomerController::class, 'store'])->middleware('permission:customers.create');
    Route::get('/customers/{id}', [CustomerController::class, 'show'])->middleware('permission:customers.view');
    Route::put('/customers/{id}', [CustomerController::class, 'update'])->middleware('permission:customers.update');
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->middleware('permission:customers.delete');

    // Company Bank Account Routes
    Route::get('/company/bank-accounts', [CompanyBankAccountController::class, 'index'])->middleware('permission:company-bank.view');
    Route::post('/company/bank-accounts', [CompanyBankAccountController::class, 'store'])->middleware('permission:company-bank.manage');
    Route::delete('/company/bank-accounts/{id}', [CompanyBankAccountController::class, 'destroy'])->middleware('permission:company-bank.manage');

    // Staff Routes
    Route::apiResource('staff-roles', StaffController::class)->middleware('permission:staff-roles.manage');
   
    // Company Routes
    Route::get('/company', [CompanyController::class, 'index'])->middleware('permission:company.view');
    Route::post('/company', [CompanyController::class, 'store'])->middleware('permission:company.manage-permissions');
    Route::get('/company/{id}', [CompanyController::class, 'show'])->middleware('permission:company.view');
    Route::put('/company/{id}', [CompanyController::class, 'update'])->middleware('permission:company.manage-permissions');
    Route::delete('/company/{id}', [CompanyController::class, 'destroy'])->middleware('permission:company.manage-permissions');


    // Attendance Management
    // Admin-managed attendance CRUD. Permission slug: attendances.manage
    Route::apiResource('attendances', AttendanceController::class)->middleware('permission:attendances.manage');
});

// Public Category Routes
Route::get('/categories/search', [CategoryController::class, 'search']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
