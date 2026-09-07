<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthCheckMiddleware;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\StoreController;

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
});

