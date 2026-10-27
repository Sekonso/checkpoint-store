<?php

use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\ProductFixtures;

it('renders a displayed product with its images and category', function () {
    $category = ProductFixtures::category();
    $product = ProductFixtures::productFor([
        'category_id' => $category->id,
        'in_display' => true,
        'stock' => 5,
    ]);

    $product->images()->createMany([
        ['filename' => 'front.webp', 'order' => 1],
        ['filename' => 'side.webp', 'order' => 2],
    ]);

    $this->get("/store/{$product->slug}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Store/show')
            ->where('product.id', $product->id)
            ->where('product.slug', $product->slug)
            ->where('product.category.id', $category->id)
            ->has('product.images', 2)
        );
});

it('renders a displayed product that is out of stock', function () {
    $product = ProductFixtures::productFor([
        'in_display' => true,
        'stock' => 0,
    ]);

    $this->get("/store/{$product->slug}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Store/show')
            ->where('product.id', $product->id)
            ->where('product.stock', 0)
        );
});

it('returns 404 for a product that is not displayed', function () {
    $product = ProductFixtures::productFor([
        'in_display' => false,
        'stock' => 5,
    ]);

    $this->get("/store/{$product->slug}")->assertNotFound();
});

it('returns 404 for an unknown product slug', function () {
    $this->get('/store/unknown-product')->assertNotFound();
});
