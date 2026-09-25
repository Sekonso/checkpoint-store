<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Webhook\MidtransWebhookController;
use App\Http\Middleware\AuthCheckMiddleware;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
});

Route::get('/about', function () {
    return Inertia::render('About');
});

Route::get('/faq', function () {
    return Inertia::render('FAQ');
});

// Blog
Route::get('/blog', [BlogController::class, 'index']);
Route::get('/blog/{article:slug}', [BlogController::class, 'show']);

// Store
Route::get('/store', [StoreController::class, 'index']);
Route::get('/store/{product:slug}', [StoreController::class, 'show']);

use App\Http\Controllers\TransactionController;

// Payment gateway webhooks (public, no auth/CSRF)
Route::prefix('webhook')->group(function () {
    Route::post('/midtrans/notification', [MidtransWebhookController::class, 'notification']);
});

Route::middleware([AuthCheckMiddleware::class])->group(function () {
    // User
    Route::get('/my-mine', [UserController::class, 'index']);
    Route::put('/user', [UserController::class, 'update']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::patch('/cart/items/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroyItem']);
    Route::delete('/cart', [CartController::class, 'clear']);

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::post('/transactions/{transaction:invoice_number}/cancel', [TransactionController::class, 'cancel']);
    Route::post('/transactions/{transaction:invoice_number}/payments', [TransactionController::class, 'init'])->middleware('throttle:12,1');
    Route::post('/transactions/{transaction:invoice_number}/payments/reset', [TransactionController::class, 'reset'])->middleware('throttle:12,1');
    Route::post('/transactions/{transaction:invoice_number}/payments/sync', [TransactionController::class, 'sync'])->middleware('throttle:12,1');
    Route::get('/transactions/{transaction:invoice_number}', [TransactionController::class, 'show']);
});
