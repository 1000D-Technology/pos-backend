<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\UserPermissionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\SupplierPaymentController;
use App\Http\Controllers\Api\SupplierPaymentDetailsController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\SalaryController;
use App\Models\User;
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
//    Artisan::call('l5-swagger:generate');
//    Artisan::call('migrate:fresh', [
//        '--seed' => true
//    ]);

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
    Route::post('/units', [UnitController::class, 'store'])->middleware('permission:units.manage');
    Route::get('/units/{id}', [UnitController::class, 'show']);
    Route::put('/units/{id}', [UnitController::class, 'update'])->middleware('permission:units.manage');
    Route::delete('/units/{id}', [UnitController::class, 'destroy'])->middleware('permission:units.manage');

    // Supplier Management
    Route::get('/suppliers/search', [SupplierController::class, 'search'])->middleware('permission:suppliers.view');
    Route::get('/suppliers', [SupplierController::class, 'index'])->middleware('permission:suppliers.view');
    Route::post('/suppliers', [SupplierController::class, 'store'])->middleware('permission:suppliers.create');
    Route::get('/suppliers/{id}', [SupplierController::class, 'show'])->middleware('permission:suppliers.view');
    Route::put('/suppliers/{id}', [SupplierController::class, 'update'])->middleware('permission:suppliers.update');
    Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])->middleware('permission:suppliers.delete');

    // Supplier Payment Management
    Route::get('/supplier-payments', [SupplierPaymentController::class, 'index'])->middleware('permission:suppliers-payments.view');
    Route::post('/supplier-payments', [SupplierPaymentController::class, 'store'])->middleware('permission:suppliers.manage-permissions');
    Route::get('/supplier-payments/{id}', [SupplierPaymentController::class, 'show'])->middleware('permission:suppliers-payments.view');
    Route::post('/supplier-payments/{id}', [SupplierPaymentController::class, 'update'])->middleware('permission:suppliers.manage-permissions');
    Route::delete('/supplier-payments/{id}', [SupplierPaymentController::class, 'destroy'])->middleware('permission:suppliers.manage-permissions');

    // Supplier Payment Details Routes
    Route::get('/supplier-payments/{id}/details', [SupplierPaymentDetailsController::class, 'index'])->middleware('permission:suppliers-payments.view');
    Route::post('/supplier-payments/{id}/details', [SupplierPaymentDetailsController::class, 'store'])->middleware('permission:suppliers.manage-permissions');
    Route::get('/supplier-payments.details/{payment_id}/{detail_id}', [SupplierPaymentDetailsController::class, 'show'])->middleware('permission:suppliers-payments.view');
    Route::post('/supplier-payments.details/{payment_id}/{detail_id}', [SupplierPaymentDetailsController::class, 'update'])->middleware('permission:suppliers.manage-permissions');
    Route::delete('/supplier-payments.details/{payment_id}/{detail_id}', [SupplierPaymentDetailsController::class, 'destroy'])->middleware('permission:suppliers.manage-permissions');


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

    // Salary Routes
    Route::get('/salaries', [SalaryController::class, 'index'])->middleware('permission:salaries.view');
    Route::post('/salaries', [SalaryController::class, 'store'])->middleware('permission:salaries.create');
    Route::get('/salaries/{id}', [SalaryController::class, 'show'])->middleware('permission:salaries.view');
    // Salary Payment Routes
    Route::get('/salary-payments', [\App\Http\Controllers\Api\SalaryPaymentController::class, 'index'])->middleware('permission:salaries.view');
    Route::post('/salary-payments', [\App\Http\Controllers\Api\SalaryPaymentController::class, 'store'])->middleware('permission:salaries.create');
    Route::get('/salary-payments/{id}', [\App\Http\Controllers\Api\SalaryPaymentController::class, 'show'])->middleware('permission:salaries.view');
    Route::delete('/salary-payments/{id}', [\App\Http\Controllers\Api\SalaryPaymentController::class, 'destroy'])->middleware('permission:salaries.create');
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

    // Product Routes
    Route::get('/products', [\App\Http\Controllers\Api\ProductController::class, 'index'])->middleware('permission:products.view');
    Route::post('/products', [\App\Http\Controllers\Api\ProductController::class, 'store'])->middleware('permission:products.create');
    Route::get('/products/{id}', [\App\Http\Controllers\Api\ProductController::class, 'show'])->middleware('permission:products.view');
    Route::put('/products/{id}', [\App\Http\Controllers\Api\ProductController::class, 'update'])->middleware('permission:products.update');
    Route::delete('/products/{id}', [\App\Http\Controllers\Api\ProductController::class, 'destroy'])->middleware('permission:products.delete');

    // Stock Routes
    Route::get('/stocks/search', [\App\Http\Controllers\Api\StockController::class, 'index'])->middleware('permission:stocks.view');
    Route::get('/stocks', [\App\Http\Controllers\Api\StockController::class, 'index'])->middleware('permission:stocks.view');
    Route::post('/stocks', [\App\Http\Controllers\Api\StockController::class, 'store'])->middleware('permission:stocks.create');
    Route::get('/stocks/{id}', [\App\Http\Controllers\Api\StockController::class, 'show'])->middleware('permission:stocks.view');
    Route::put('/stocks/{id}', [\App\Http\Controllers\Api\StockController::class, 'update'])->middleware('permission:stocks.update');

    // Attendance Management
    // Admin-managed attendance CRUD. Permission slug: attendances.manage
    Route::apiResource('attendances', AttendanceController::class)->middleware('permission:attendances.manage');
});

// Public Category Routes
Route::get('/categories/search', [CategoryController::class, 'search']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

// Public Product Routes (search endpoint only - main CRUD requires auth)
Route::get('/products/search', [\App\Http\Controllers\Api\ProductController::class, 'index']);

