<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FileUploadController;
use App\Http\Controllers\Api\LocaleController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\ClientController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\OrderController;
use App\Http\Controllers\Api\Admin\InventoryController;
use App\Http\Controllers\Api\Public\ProductController as PublicProductController;
use App\Http\Controllers\Api\Public\ClientController as PublicClientController;
use App\Http\Controllers\Api\Public\OrderController as PublicOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Locale API routes (no authentication required)
Route::prefix('locale')->group(function () {
    Route::get('/', [LocaleController::class, 'index']);
    Route::get('/translations', [LocaleController::class, 'translations']);
    Route::get('/validation', [LocaleController::class, 'validationMessages']);
    Route::get('/auth', [LocaleController::class, 'authMessages']);
});

// Public API routes (no authentication required)
Route::prefix('public')->group(function () {
    // Product routes
    Route::get('/products', [PublicProductController::class, 'index']);
    Route::get('/products/{id}', [PublicProductController::class, 'show']);
    Route::post('/products/{id}/availability', [PublicProductController::class, 'checkAvailability']);
    Route::get('/products/{id}/stock', [PublicProductController::class, 'getStock']);
    Route::get('/products-search', [PublicProductController::class, 'search']);
    Route::get('/products-featured', [PublicProductController::class, 'featured']);
    Route::get('/products-filters', [PublicProductController::class, 'filterOptions']);

    // Client routes
    Route::post('/clients', [PublicClientController::class, 'store']);
    Route::get('/clients/by-email', [PublicClientController::class, 'getByEmail']);

    // Order routes
    Route::post('/orders', [PublicOrderController::class, 'store']);
    Route::get('/orders/{id}', [PublicOrderController::class, 'show']);
    Route::get('/orders-track', [PublicOrderController::class, 'trackByEmail']);
    Route::post('/orders-validate-cart', [PublicOrderController::class, 'validateCart']);

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
        Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'user']);
    });
});

// File upload routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/upload', [FileUploadController::class, 'upload']);
    Route::delete('/upload', [FileUploadController::class, 'delete']);
    Route::get('/upload/info', [FileUploadController::class, 'info']);
});

// Admin API routes (authentication and admin role required)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // User management routes
    Route::apiResource('users', UserController::class);

    // Client management routes
    Route::apiResource('clients', ClientController::class);
    Route::get('clients/{client}/orders', [ClientController::class, 'orders']);
    Route::get('clients/{client}/statistics', [ClientController::class, 'statistics']);

    // Product management routes
    Route::apiResource('products', ProductController::class);
    Route::get('products-categories', [ProductController::class, 'categories']);
    Route::get('products-brands', [ProductController::class, 'brands']);
    Route::get('products-colors', [ProductController::class, 'colors']);
    Route::patch('products-bulk-status', [ProductController::class, 'bulkUpdateStatus']);

    // Order management routes
    Route::apiResource('orders', OrderController::class);
    Route::patch('orders/{order}/approve', [OrderController::class, 'approve']);
    Route::patch('orders/{order}/reject', [OrderController::class, 'reject']);
    Route::patch('orders/{order}/complete', [OrderController::class, 'complete']);
    Route::get('orders-statistics', [OrderController::class, 'statistics']);
    Route::get('orders-status-counts', [OrderController::class, 'statusCounts']);

    // Inventory management routes
    Route::get('inventory', [InventoryController::class, 'index']);
    Route::get('inventory/{inventory}', [InventoryController::class, 'show']);
    Route::patch('inventory/{inventory}', [InventoryController::class, 'update']);
    Route::patch('inventory/products/{product}/stock', [InventoryController::class, 'updateStock']);
    Route::get('inventory-low-stock', [InventoryController::class, 'lowStockAlerts']);
    Route::get('inventory-out-of-stock', [InventoryController::class, 'outOfStockItems']);
    Route::get('inventory-statistics', [InventoryController::class, 'statistics']);
    Route::patch('inventory-bulk-update', [InventoryController::class, 'bulkUpdateStock']);
    Route::get('inventory-movements', [InventoryController::class, 'stockMovements']);
    Route::get('inventory-report', [InventoryController::class, 'report']);
});
