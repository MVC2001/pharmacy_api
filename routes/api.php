<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
 use App\Http\Controllers\RoleController;
 use App\Http\Controllers\StoreController;
 use App\Http\Controllers\PermissionController;
 use App\Http\Controllers\ItemCategoryController;
 use App\Http\Controllers\ItemController;
 use App\Http\Controllers\PurchaseController;
 use App\Http\Controllers\DarSHowRoomController;
use App\Http\Controllers\DodomaSHowRoomController;
use App\Http\Controllers\DarSaleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentsInDarShowRoom;
use App\Http\Controllers\PaymentsInDodomaShowRoom;
use App\Http\Controllers\DodomaSalesController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\PurchasesReportController;
use App\Http\Controllers\StockHistory;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\MedicineCategoryController;
use App\Http\Controllers\MedicineInventoryController;
use App\Http\Controllers\MedicineStockController;

// Public Routes
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthorized user! Please login to access the API'], 401);
})->name('login');


// Authentication Routes
Route::post('/auth/add_user', [AuthController::class, 'register']);
Route::post('/auth/login_v2', [AuthController::class, 'login']);
Route::post('/auth/log-activity', [AuthController::class, 'logUserActivity']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::post('/auth/request-reset', [PasswordResetController::class, 'requestPasswordReset']);
Route::post('/auth/password-reset', [PasswordResetController::class, 'resetPassword']);


// Protected Routes
Route::middleware(['auth:sanctum', 'token.expiration'])->group(function () {

    Route::get('/get_login_username', [AuthController::class, 'getLoggedUserName']);
     Route::get('/auth/users', [AuthController::class, 'users']);
    Route::get('/auth/profile', [AuthController::class, 'getLoggedUserProfile']);
    Route::apiResource('/auth/roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);
    Route::get('/user/permissions', [PermissionController::class, 'getUserPermissions']);

    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
     // Fetch user by ID
    // Route to show a specific user by user_id
    Route::get('user/{user_id}', [AuthController::class, 'showUserById']);
    // Route to update a specific user by user_id
    Route::put('user/{user_id}', [AuthController::class, 'updateUser']);
    Route::delete('/auth/user/{user_id}', [AuthController::class, 'deleteUser']);
    Route::get('/auth/audit-trail', [AuthController::class, 'getAuditTrail']);



//medicine Routes
Route::apiResource('medicine-categories', MedicineCategoryController::class);
Route::apiResource('medicine-inventories', MedicineInventoryController::class);

 Route::get('/stocks', [MedicineStockController::class, 'index']); // View all stocks
    Route::get('/stocks/{id}', [MedicineStockController::class, 'show']); // Show stock by ID
    Route::post('/stocks', [MedicineStockController::class, 'store']); // Add new stock
    Route::put('/stocks/{id}', [MedicineStockController::class, 'update']); // Update stock
    Route::delete('/stocks/{id}', [MedicineStockController::class, 'destroy']);
});
