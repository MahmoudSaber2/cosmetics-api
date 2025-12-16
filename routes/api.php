<?php

use App\Http\Controllers\Api\V1\Admin\Auth\LoginController;
use App\Http\Controllers\Api\V1\Admin\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Admin\BrandController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Admin\ClientController;
use App\Http\Controllers\Api\V1\Admin\ProductController;
use App\Http\Controllers\Api\V1\Admin\ProductMediaController;
use App\Http\Controllers\Api\V1\Admin\SetProductMediaAsMainController;
use App\Http\Controllers\Api\V1\Admin\OrderController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\SelectController;
use App\Http\Controllers\Api\V1\Website\HomeController as WebsiteHomeController;
use App\Http\Controllers\Api\V1\Website\ProductController as WebsiteProductController;

use App\Http\Controllers\Api\V1\Website\OrderController as WebsiteOrderController;

// V2 Controllers
use App\Http\Controllers\Api\V2\Admin\Auth\LoginController as V2LoginController;
use App\Http\Controllers\Api\V2\Admin\Auth\LogoutController as V2LogoutController;
use App\Http\Controllers\Api\V2\Admin\BrandController as V2BrandController;
use App\Http\Controllers\Api\V2\Admin\CategoryController as V2CategoryController;
use App\Http\Controllers\Api\V2\Admin\UserController as V2UserController;
use App\Http\Controllers\Api\V2\Admin\ClientController as V2ClientController;
use App\Http\Controllers\Api\V2\Admin\ProductController as V2ProductController;
use App\Http\Controllers\Api\V2\Admin\ProductMediaController as V2ProductMediaController;
use App\Http\Controllers\Api\V2\Admin\SetProductMediaAsMainController as V2SetProductMediaAsMainController;
use App\Http\Controllers\Api\V2\Admin\OrderController as V2OrderController;
use App\Http\Controllers\Api\V2\Admin\DashboardController as V2DashboardController;
use App\Http\Controllers\Api\V2\SelectController as V2SelectController;
use App\Http\Controllers\Api\V2\Website\HomeController as V2WebsiteHomeController;
use App\Http\Controllers\Api\V2\Website\ProductController as V2WebsiteProductController;
use App\Http\Controllers\Api\V2\Website\OrderController as V2WebsiteOrderController;
use App\Http\Controllers\Api\V2\Website\PaymentController as V2WebsitePaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
| API Versioning:
| - v1: Original API endpoints for admin panel and basic website functionality
| - v2: Enhanced API endpoints with payment processing and advanced features
|
*/

/*
|--------------------------------------------------------------------------
| API Version 1 Routes
|--------------------------------------------------------------------------
| Original API endpoints for admin panel and basic website functionality
*/
Route::prefix('v1/')
    ->middleware('locale')
    ->group(function () {

        // ==================== ADMIN ROUTES ====================
        Route::prefix('admin')->group(function () {

            // ---------- AUTHENTICATION ----------
            Route::prefix('auth')->group(function () {
                Route::post('/login', LoginController::class);
                Route::post('/logout', LogoutController::class);
            });

            // ---------- USER MANAGEMENT ----------
            Route::apiResource('users', UserController::class);

            // ---------- BRAND MANAGEMENT ----------
            Route::apiResource('brands', BrandController::class);
            Route::put('brands/{id}/restore', [BrandController::class, 'restore']);
            Route::delete('brands/{id}/force-delete', [BrandController::class, 'forceDelete']);

            // ---------- CATEGORY MANAGEMENT ----------
            Route::apiResource('categories', CategoryController::class);

            // ---------- PRODUCT MANAGEMENT ----------
            Route::apiResource('products', ProductController::class);

            // ---------- PRODUCT MEDIA MANAGEMENT ----------
            Route::get('products/{product}/media', [ProductMediaController::class, 'index']);
            Route::post('products/{product}/media', [ProductMediaController::class, 'store']);
            Route::delete('products/{product}/media/{media}', [ProductMediaController::class, 'destroy']);
            Route::put('products/{product}/media/{media}/set-main', SetProductMediaAsMainController::class);

            // ---------- CLIENT MANAGEMENT ----------
            Route::apiResource('clients', ClientController::class);

            // ---------- ORDER MANAGEMENT ----------
            Route::apiResource('orders', OrderController::class);
            Route::put('orders/{order}/approve', [OrderController::class, 'approve']);
            Route::put('orders/{order}/reject', [OrderController::class, 'reject']);
            Route::put('orders/{order}/complete', [OrderController::class, 'complete']);
            Route::get('orders-statistics', [OrderController::class, 'statistics']);
            Route::get('orders-status-counts', [OrderController::class, 'statusCounts']);

            // ---------- DASHBOARD ----------
            Route::get('dashboard', [DashboardController::class, 'index']);

        });

        // ==================== WEBSITE ROUTES ====================
        Route::prefix('website')->group(function () {

            // ---------- HOME PAGE ----------
            Route::get('/home', [WebsiteHomeController::class, 'index']);

            // ---------- PRODUCT CATALOG ----------
            Route::get('/products', [WebsiteProductController::class, 'index']);
            Route::get('/products/{product:slug}', [WebsiteProductController::class, 'show']);
            Route::get('/products/{product:slug}/related', [WebsiteProductController::class, 'related']);

            // ---------- ORDER PROCESSING ----------
            Route::post('/orders', [WebsiteOrderController::class, 'store']);
            Route::post('/orders/validate-cart', [WebsiteOrderController::class, 'validateCart']);

        });

        // ==================== UTILITY ROUTES ====================
        Route::get('selects', [SelectController::class, 'getSelects']);

    });

/*
|--------------------------------------------------------------------------
| API Version 2 Routes
|--------------------------------------------------------------------------
| Enhanced API endpoints with payment processing and advanced features
*/
Route::prefix('v2/')
    ->middleware('locale')
    ->group(function () {

        // ==================== ADMIN ROUTES V2 ====================
        Route::prefix('admin')->group(function () {

            // ---------- AUTHENTICATION ----------
            Route::prefix('auth')->group(function () {
                Route::post('/login', V2LoginController::class);
                Route::post('/logout', V2LogoutController::class);
            });

            // ---------- USER MANAGEMENT ----------
            Route::apiResource('users', V2UserController::class);

            // ---------- BRAND MANAGEMENT ----------
            Route::apiResource('brands', V2BrandController::class);
            Route::put('brands/{id}/restore', [V2BrandController::class, 'restore']);
            Route::delete('brands/{id}/force-delete', [V2BrandController::class, 'forceDelete']);

            // ---------- CATEGORY MANAGEMENT ----------
            Route::apiResource('categories', V2CategoryController::class);

            // ---------- PRODUCT MANAGEMENT ----------
            Route::apiResource('products', V2ProductController::class);

            // ---------- PRODUCT MEDIA MANAGEMENT ----------
            Route::get('products/{product}/media', [V2ProductMediaController::class, 'index']);
            Route::post('products/{product}/media', [V2ProductMediaController::class, 'store']);
            Route::delete('products/{product}/media/{media}', [V2ProductMediaController::class, 'destroy']);
            Route::put('products/{product}/media/{media}/set-main', V2SetProductMediaAsMainController::class);

            // ---------- CLIENT MANAGEMENT ----------
            Route::apiResource('clients', V2ClientController::class);

            // ---------- ORDER MANAGEMENT ----------
            Route::apiResource('orders', V2OrderController::class);
            Route::put('orders/{order}/approve', [V2OrderController::class, 'approve']);
            Route::put('orders/{order}/reject', [V2OrderController::class, 'reject']);
            Route::put('orders/{order}/complete', [V2OrderController::class, 'complete']);
            Route::get('orders-statistics', [V2OrderController::class, 'statistics']);
            Route::get('orders-status-counts', [V2OrderController::class, 'statusCounts']);

            // ---------- DASHBOARD ----------
            Route::get('dashboard', [V2DashboardController::class, 'index']);

        });

        // ==================== WEBSITE ROUTES V2 ====================
        Route::prefix('website')->group(function () {

            // ---------- HOME PAGE ----------
            Route::get('/home', [V2WebsiteHomeController::class, 'index']);

            // ---------- PRODUCT CATALOG ----------
            Route::get('/products', [V2WebsiteProductController::class, 'index']);
            Route::get('/products/{product:slug}', [V2WebsiteProductController::class, 'show']);
            Route::get('/products/{product:slug}/related', [V2WebsiteProductController::class, 'related']);

            // ---------- ORDER PROCESSING ----------
            Route::post('/orders', [V2WebsiteOrderController::class, 'store']);
            Route::post('/orders/validate-cart', [V2WebsiteOrderController::class, 'validateCart']);

            // ---------- PAYMENT PROCESSING ----------
            Route::post('orders/{order}/payment/create-intent', [V2WebsitePaymentController::class, 'createPaymentIntent']);
            Route::post('payments/{payment}/confirm', [V2WebsitePaymentController::class, 'confirmPayment']);
            Route::get('payments/{payment}/status', [V2WebsitePaymentController::class, 'getPaymentStatus']);

            // ---------- PAYMENT WEBHOOKS ----------
            Route::post('payments/webhook/stripe', [V2WebsitePaymentController::class, 'handleStripeWebhook']);

        });

        // ==================== UTILITY ROUTES V2 ====================
        Route::get('selects', [V2SelectController::class, 'getSelects']);

        // ==================== FUTURE V2 ENHANCEMENTS ====================
        // Additional v2 routes can be added here as the API evolves
        // Examples:
        // - Enhanced product search with filters and AI recommendations
        // - Advanced order tracking with real-time updates
        // - Customer account management with loyalty programs
        // - Advanced analytics and reporting endpoints
        // - Multi-language and multi-currency support
        // - Advanced inventory management with forecasting

    });
