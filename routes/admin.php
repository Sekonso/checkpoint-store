<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Middleware\AdminCheckMiddleware;
use App\Http\Controllers\Admin\ArticleController;

Route::middleware([AdminCheckMiddleware::class])->group(function () {
    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    });

    // Articles
    Route::get('/admin/articles', [ArticleController::class, 'index']);
    Route::get('/admin/articles/create', [ArticleController::class, 'create']);
    Route::get('/admin/articles/{article}/edit', [ArticleController::class, 'edit']);
    
    Route::post('/admin/articles', [ArticleController::class, 'store']);

    Route::patch('/admin/articles/{article}/update', [ArticleController::class, 'update']);
    Route::patch('/admin/articles/{article}/archive', [ArticleController::class, 'archive']);

    Route::delete('/admin/articles/{article}', [ArticleController::class, 'destroy']);
});
