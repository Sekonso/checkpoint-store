<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

final class AuthService
{
    /**
     * Create a user account from validated registration data.
     *
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function register(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
    }

    /**
     * Attempt to authenticate a user by name and password.
     *
     * @param  array{name: string, password: string}  $credentials
     */
    public function attemptLogin(array $credentials): bool
    {
        return Auth::attempt([
            'name' => $credentials['name'],
            'password' => $credentials['password'],
        ]);
    }

    /** End the current web guard session. */
    public function logout(): void
    {
        Auth::guard('web')->logout();
    }
}
