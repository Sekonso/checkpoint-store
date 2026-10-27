<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

it('signs out the authenticated user and redirects home', function () {
    $this->actingAs(User::factory()->create())
        ->post('/sign-out')
        ->assertRedirect('/');

    expect(Auth::check())->toBeFalse();
});
