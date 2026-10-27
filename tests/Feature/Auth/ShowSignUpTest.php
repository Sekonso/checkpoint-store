<?php

it('renders the sign up page', function () {
    $this->get('/sign-up')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Auth/SignUp'));
});
