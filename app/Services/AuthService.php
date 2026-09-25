<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

final class AuthService
{
    public function register(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
    }

    public function attemptLogin(array $credentials): bool
    {
        return Auth::attempt([
            'name' => $credentials['name'],
            'password' => $credentials['password'],
        ]);
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
    }
}
