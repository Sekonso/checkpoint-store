<?php

it('renders the sign in page', function () {
    $this->get('/sign-in')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Auth/SignIn'));
});
