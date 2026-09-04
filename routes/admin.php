<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Middleware\AdminCheckMiddleware;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\ProductController;

Route::middleware([AdminCheckMiddleware::class])->group(function () {
    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    });

    // Articles
    Route::get('/admin/articles', [ArticleController::class, 'index']);
    Route::get('/admin/articles/create', [ArticleController::class, 'create']);
    Route::get('/admin/articles/{article}/edit', [ArticleController::class, 'edit']);
    
    Route::post('/admin/articles', [ArticleController::class, 'store']);

    Route::put('/admin/articles/{article}', [ArticleController::class, 'update']);
    Route::patch('/admin/articles/{article}/archive', [ArticleController::class, 'archive']);

    Route::delete('/admin/articles/{article}', [ArticleController::class, 'destroy']);

    // Products
    Route::get('/admin/products', [ProductController::class, 'index']);
    Route::get('/admin/products/create', [ProductController::class, 'create']);
    Route::get('/admin/products/{product}/edit', [ProductController::class, 'edit']);
    
    Route::post('/admin/products', [ProductController::class, 'store']);

    Route::put('/admin/products/{product}', [ProductController::class, 'update']);

    Route::delete('/admin/products/{product}', [ProductController::class, 'destroy']);
});
