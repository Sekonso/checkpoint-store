<?php

use App\Models\User;

it('renders the authenticated user profile page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/my-mine')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('User/index'));
});
