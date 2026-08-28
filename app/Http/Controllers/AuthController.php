<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\SignUpRequest;
use App\Http\Requests\SignInRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthController extends Controller
{
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

            if (app()->environment(['local', 'development'])) {
                throw $e;
            }

            return back()->with('form_error', 'Server failed to submit your data');
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
                return redirect()->back()->with('form_error', 'Invalid password or username');
            }

            $request->session()->regenerate();

            return redirect('/');
        } catch (Throwable $e) {
            report($e);

            if (app()->environment(['local', 'development'])) {
                throw $e;
            }

            return back()->with('form_error', 'Server failed to submit your data');
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
