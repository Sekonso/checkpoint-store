<?php

use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\ProductFixtures;
use Tests\Support\UserFixtures;

it('renders the product edit form with the product and categories', function () {
    $admin = UserFixtures::admin();
    $categories = ProductFixtures::categories();
    $product = ProductFixtures::productFor([
        'name' => 'Edit Me',
        'category_id' => $categories->first()->id,
    ]);

    $this->actingAs($admin)
        ->get("/admin/products/{$product->id}/edit")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Products/edit')
            ->where('product.id', $product->id)
            ->has('productCategories', 2)
        );
});

it('returns 404 for an unknown product', function () {
    $this->actingAs(UserFixtures::admin())
        ->get('/admin/products/999999/edit')
        ->assertNotFound();
});
