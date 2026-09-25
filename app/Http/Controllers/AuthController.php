<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignInRequest;
use App\Http\Requests\SignUpRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function signUp(SignUpRequest $request)
    {
        try {
            $user = $this->auth->register($request->validated());

            Auth::login($user);

            $request->session()->regenerate();

            return redirect('/');
        } catch (Throwable $e) {
            report($e);

            if (app()->hasDebugModeEnabled()) {
                throw $e;
            }

            return back()->with('form_error', 'Server failed to submit your data');
        }
    }

    public function signIn(SignInRequest $request)
    {
        try {
            $validated = $request->validated();

            if (! $this->auth->attemptLogin($validated)) {
                return redirect()->back()->with('form_error', 'Invalid password or username');
            }

            $request->session()->regenerate();

            return redirect('/');
        } catch (Throwable $e) {
            report($e);

            if (app()->hasDebugModeEnabled()) {
                throw $e;
            }

            return back()->with('form_error', 'Server failed to submit your data');
        }
    }

    public function signOut(Request $request): RedirectResponse
    {
        $this->auth->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
