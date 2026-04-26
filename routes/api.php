<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\PubTableController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\LedMessageController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\StaffShiftController;
use App\Http\Controllers\Api\ReviewController;


// ================= PUBLIC ROUTES =================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public data (no auth needed)
Route::prefix('public')->group(function () {
    Route::get('/menu-items', [MenuItemController::class, 'publicIndex']);
    Route::get('/banners', [BannerController::class, 'active']);
    Route::get('/led-messages', [LedMessageController::class, 'active']);
    Route::get('/events', [EventController::class, 'active']);
});


// ================= PROTECTED ROUTES =================

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ================= CART =================
    Route::get('/cart', [CartController::class, 'myCart']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::patch('/cart/items/{id}', [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{id}', [CartController::class, 'removeItem']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    Route::post('/cart/checkout', [CartController::class, 'checkout']);

    // ================= RESERVATIONS =================
    Route::prefix('reservations')->middleware('role:any')->group(function () {
        Route::get('/', [ReservationController::class, 'index']);
        Route::post('/', [ReservationController::class, 'store']);
        Route::get('/{reservation}', [ReservationController::class, 'show']);
    });

    Route::middleware('role:admin,staff')->group(function () {
        Route::patch('/reservations/{id}/status', [ReservationController::class, 'updateStatus']);
        Route::patch('/reservations/{id}', [ReservationController::class, 'update']);
        Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
    });

    // ================= VIEW ONLY =================
    Route::apiResources([
        'categories'   => CategoryController::class,
        'menu-items'   => MenuItemController::class,
        'pub-tables'   => PubTableController::class,
        'banners'      => BannerController::class,
        'led-messages' => LedMessageController::class,
        'events'       => EventController::class,
        'orders'       => OrderController::class,
        'payments'     => PaymentController::class,
        'reviews'      => ReviewController::class, // 
    ], ['only' => ['index', 'show']]);

    // ================= CUSTOMER ACTION =================
    // create review
    Route::post('/reviews', [ReviewController::class, 'store']);

    // ================= ADMIN & STAFF =================
    Route::middleware('role:admin,staff')->group(function () {

        Route::apiResources([
            'categories'   => CategoryController::class,
            'menu-items'   => MenuItemController::class,
            'pub-tables'   => PubTableController::class,
            'banners'      => BannerController::class,
            'led-messages' => LedMessageController::class,
            'events'       => EventController::class,
            'orders'       => OrderController::class,
            'payments'     => PaymentController::class,
        ], ['except' => ['index', 'show']]);

        // reviews (update & delete only)
        Route::apiResource('reviews', ReviewController::class)
            ->except(['index', 'show', 'store']);

        // staff shifts (full CRUD)
        Route::apiResource('staff-shifts', StaffShiftController::class);

        // extra actions
        Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    });
});
