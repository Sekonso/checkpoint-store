<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

it('authenticates a user with valid credentials', function () {
    $user = User::factory()->create([
        'name' => 'existing_gamer',
        'password' => 'SecurePass1!',
    ]);

    $this->post('/sign-in', [
        'name' => 'existing_gamer',
        'password' => 'SecurePass1!',
    ])->assertRedirect('/');

    expect(Auth::id())->toBe($user->id);
});

it('returns a form error for invalid credentials', function () {
    User::factory()->create(['name' => 'existing_gamer']);

    $this->from('/sign-in')
        ->post('/sign-in', [
            'name' => 'existing_gamer',
            'password' => 'wrong-password',
        ])
        ->assertRedirect('/sign-in')
        ->assertSessionHas('form_error', 'Invalid password or username');

    expect(Auth::check())->toBeFalse();
});

it('validates missing sign in credentials', function (string $field) {
    $this->from('/sign-in')
        ->post('/sign-in', ['name' => 'existing_gamer', 'password' => 'SecurePass1!'] + [$field => ''])
        ->assertRedirect('/sign-in');

    expect(Session::has('errors'))->toBeFalse();
})->with(['name', 'password']);
