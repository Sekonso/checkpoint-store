<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;

Route::get('/sign-up', function () {
    return Inertia::render('Auth/SignUp');
});

Route::get('/sign-in', function () {
    return Inertia::render('Auth/SignIn');
});

Route::post('/sign-up', [AuthController::class, 'signUp']);

Route::post('/sign-in', [AuthController::class, 'signIn']);

Route::post('/sign-out', [AuthController::class, 'signOut']);
