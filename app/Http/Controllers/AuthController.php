<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\SignUpRequest;
use App\Http\Requests\SignInRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Handle the incoming request.
     */

    public function signUp(SignUpRequest $request)
    {
        try {
            $validated = $request->validated();

            $newUser = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            Auth::login($newUser);

            $request->session()->regenerate();

            return redirect('/');
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', "Server failed to submit your data");
        }
    }

    public function signIn(SignInRequest $request)
    {
        try {
            $validated = $request->validated();

            $credentials = [
                'name' => $validated['name'],
                'password' => $validated['password'],
            ];

            if (!Auth::attempt($credentials)) {
                return back()->with(
                    'error',
                    'Invalid username or password.'
                );
            }

            $request->session()->regenerate();

            return redirect('/');
        } catch (Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Server failed to submit your data.'
            );
        }
    }
    public function signOut(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
