<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('User/index');
    }

    public function update(UpdateUserRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            if (blank($validated['password'] ?? null)) {
                unset($validated['password']);
            }

            $user->update($validated);

            return redirect('/my-mine')->with('toast', [
                'type' => 'success',
                'message' => 'Profile updated successfully.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            if (app()->environment(['local', 'development'])) {
                throw $e;
            }

            return redirect('/my-mine')->with('toast', [
                'type' => 'error',
                'message' => 'Server failed to update profile.',
            ]);
        }
    }
}
