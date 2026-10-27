<?php

use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\ProductFixtures;
use Tests\Support\UserFixtures;

it('renders the product creation form with categories', function () {
    $admin = UserFixtures::admin();

    ProductFixtures::categories();

    $this->actingAs($admin)
        ->get('/admin/products/create')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Products/create')
            ->has('productCategories', 2)
        );
});
