<?php

use App\Http\Controllers\Api\V1\Admin\Auth\LoginController;
use App\Http\Controllers\Api\V1\Admin\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Admin\BrandController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Admin\ClientController;
use App\Http\Controllers\Api\V1\Admin\ProductController;
use App\Http\Controllers\Api\V1\Admin\OrderController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\SelectController;
use App\Http\Controllers\Api\V1\Website\HomeController as WebsiteHomeController;
use App\Http\Controllers\Api\V1\Website\ProductController as WebsiteProductController;
use App\Http\Controllers\Api\V1\Website\CategoryController as WebsiteCategoryController;
use App\Http\Controllers\Api\V1\Website\OrderController as WebsiteOrderController;
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

// API Version 1 Routes
// Route::prefix('v1')->group(function () {
//     // Locale API routes (no authentication required)
//     Route::prefix('locale')->group(function () {
//         Route::get('/', [LocaleController::class, 'index']);
//         Route::get('/translations', [LocaleController::class, 'translations']);
//         Route::get('/validation', [LocaleController::class, 'validationMessages']);
//         Route::get('/auth', [LocaleController::class, 'authMessages']);
//     });

//     // Authentication routes
//     Route::prefix('auth')->group(function () {
//         Route::post('/login', [AuthController::class, 'login']);
//         Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
//         Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'user']);
//     });

//     // Public API routes (no authentication required)
//     Route::prefix('public')->group(function () {
//         // Product routes
//         Route::get('/products', [PublicProductController::class, 'index']);
//         Route::get('/products/{id}', [PublicProductController::class, 'show']);
//         Route::post('/products/{id}/availability', [PublicProductController::class, 'checkAvailability']);
//         Route::get('/products/{id}/stock', [PublicProductController::class, 'getStock']);
//         Route::get('/products-search', [PublicProductController::class, 'search']);
//         Route::get('/products-featured', [PublicProductController::class, 'featured']);
//         Route::get('/products-filters', [PublicProductController::class, 'filterOptions']);

//         // Client routes
//         Route::post('/clients', [PublicClientController::class, 'store']);
//         Route::get('/clients/by-email', [PublicClientController::class, 'getByEmail']);

//         // Order routes
//         Route::post('/orders', [PublicOrderController::class, 'store']);
//         Route::get('/orders/{id}', [PublicOrderController::class, 'show']);
//         Route::get('/orders-track', [PublicOrderController::class, 'trackByEmail']);
//         Route::post('/orders-validate-cart', [PublicOrderController::class, 'validateCart']);
//     });

//     // File upload routes (authentication required)
//     Route::middleware('auth:sanctum')->group(function () {
//         Route::post('/upload', [FileUploadController::class, 'upload']);
//         Route::delete('/upload', [FileUploadController::class, 'delete']);
//         Route::get('/upload/info', [FileUploadController::class, 'info']);
//     });

//     // Admin API routes (authentication and admin role required)
//     Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
//         // User management routes
//         Route::apiResource('users', UserController::class);
//         Route::patch('users/{user}/status', [UserController::class, 'updateStatus']);

//         // Client management routes
//         Route::apiResource('clients', ClientController::class);
//         Route::get('clients/{client}/orders', [ClientController::class, 'orders']);
//         Route::get('clients/{client}/statistics', [ClientController::class, 'statistics']);

//         // Product management routes
//         Route::apiResource('products', ProductController::class);
//         Route::get('products-categories', [ProductController::class, 'categories']);
//         Route::get('products-brands', [ProductController::class, 'brands']);
//         Route::get('products-colors', [ProductController::class, 'colors']);
//         Route::patch('products-bulk-status', [ProductController::class, 'bulkUpdateStatus']);

//         // Order management routes
//         Route::apiResource('orders', OrderController::class);
//         Route::patch('orders/{order}/approve', [OrderController::class, 'approve']);
//         Route::patch('orders/{order}/reject', [OrderController::class, 'reject']);
//         Route::patch('orders/{order}/complete', [OrderController::class, 'complete']);
//         Route::get('orders-statistics', [OrderController::class, 'statistics']);
//         Route::get('orders-status-counts', [OrderController::class, 'statusCounts']);

//         // Inventory management routes
//         Route::get('inventory', [InventoryController::class, 'index']);
//         Route::get('inventory/{inventory}', [InventoryController::class, 'show']);
//         Route::patch('inventory/{inventory}', [InventoryController::class, 'update']);
//         Route::patch('inventory/products/{product}/stock', [InventoryController::class, 'updateStock']);
//         Route::get('inventory-low-stock', [InventoryController::class, 'lowStockAlerts']);
//         Route::get('inventory-out-of-stock', [InventoryController::class, 'outOfStockItems']);
//         Route::get('inventory-statistics', [InventoryController::class, 'statistics']);
//         Route::patch('inventory-bulk-update', [InventoryController::class, 'bulkUpdateStock']);
//         Route::get('inventory-movements', [InventoryController::class, 'stockMovements']);
//         Route::get('inventory-report', [InventoryController::class, 'report']);
//     });
// });

Route::prefix('v1/')
    ->middleware('locale')
    ->group(function () {

        // -------------------- ADMIN ROUTES --------------------
        Route::prefix('admin')->group(function () {

            // ---------- AUTH ----------
            Route::prefix('auth')->group(function () {
                Route::post('/login', LoginController::class);
                Route::post('/logout', LogoutController::class);
            });

            // ---------- USERS ----------
            // Route::get('users/{id}/view', [UserController::class, 'userView']);
            Route::apiResource('users', UserController::class);
            // Route::apiSingleton('profile', UserProfileController::class);
            // Route::put('profile/change-password', ChangeCurrentPasswordController::class);
            // Route::post('users/bulk-action', UserBulkActionController::class);

            // ---------- BRANDS ----------
            Route::apiResource('brands', BrandController::class);

            // ---------- CATEGORIES ----------
            Route::apiResource('categories', CategoryController::class);

            // ---------- PRODUCTS ----------
            Route::apiResource('products', ProductController::class);

            // ---------- CLIENTS ----------
            Route::apiResource('clients', ClientController::class);

            // ---------- ORDERS ----------
            Route::apiResource('orders', OrderController::class);
            Route::put('orders/{order}/approve', [OrderController::class, 'approve']);
            Route::put('orders/{order}/reject', [OrderController::class, 'reject']);
            Route::put('orders/{order}/complete', [OrderController::class, 'complete']);
            Route::get('orders-statistics', [OrderController::class, 'statistics']);
            Route::get('orders-status-counts', [OrderController::class, 'statusCounts']);

            // ---------- DASHBOARD ----------
            Route::get('dashboard', [DashboardController::class, 'index']);

        });

        // -------------------- WEBSITE ROUTES --------------------
        Route::prefix('website')->group(function () {

            // ---------- HOME PAGE ----------
            Route::get('/home', [WebsiteHomeController::class, 'index']);

            // ---------- CATEGORIES ----------
            //Route::get('/categories', [WebsiteCategoryController::class, 'index']);
            //Route::get('/categories/{slug}', [WebsiteCategoryController::class, 'show']);

            // ---------- PRODUCTS ----------
            Route::get('/products', [WebsiteProductController::class, 'index']);
            Route::get('/products/{product:slug}', [WebsiteProductController::class, 'show']);
            Route::get('/products/{product:slug}/related', [WebsiteProductController::class, 'related']);

            // ---------- ORDERS ----------
            Route::post('/orders', [WebsiteOrderController::class, 'store']);
            //Route::get('/orders/{orderNumber}', [WebsiteOrderController::class, 'show']);
            //Route::post('/orders/track', [WebsiteOrderController::class, 'track']);
            //Route::post('/orders/client-orders', [WebsiteOrderController::class, 'clientOrders']);
            Route::post('/orders/validate-cart', [WebsiteOrderController::class, 'validateCart']);
            //Route::post('/orders/{orderNumber}/cancel', [WebsiteOrderController::class, 'cancel']);

        });

        // ---------- SELECTS ----------
        Route::get('v1/selects', [SelectController::class, 'index']);
    });
