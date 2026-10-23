<?php

use App\Models\Product;
use App\Models\ProductCategory;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\ProductFixtures;

it('lists only displayed products that are in stock', function () {
    $visible = ProductFixtures::productFor([
        'in_display' => true,
        'stock' => 5,
    ]);
    ProductFixtures::productFor([
        'in_display' => false,
        'stock' => 5,
    ]);
    ProductFixtures::productFor([
        'in_display' => true,
        'stock' => 0,
    ]);

    $this->get('/store')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Store/index')
            ->where('querySearch', null)
            ->where('queryCategory', null)
            ->has('products.data', 1)
            ->where('products.data.0.id', $visible->id)
            ->where('products.total', 1)
        );
});

it('paginates products twelve per page', function () {
    Product::factory()->count(13)->create([
        'category_id' => ProductFixtures::category()->id,
        'in_display' => true,
        'stock' => 5,
    ]);

    $this->get('/store')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 12)
            ->where('products.total', 13)
        );
});

it('orders products newest first', function () {
    $now = now();

    $this->travelTo($now->copy()->subDay());
    $older = ProductFixtures::productFor([
        'in_display' => true,
        'stock' => 5,
    ]);

    $this->travelTo($now);
    $newer = ProductFixtures::productFor([
        'in_display' => true,
        'stock' => 5,
    ]);

    $this->get('/store')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('products.data.0.id', $newer->id)
            ->where('products.data.1.id', $older->id)
        );
});

it('filters products by a name search', function () {
    $match = ProductFixtures::productFor([
        'name' => 'Gaming Mouse',
        'in_display' => true,
        'stock' => 5,
    ]);
    ProductFixtures::productFor([
        'name' => 'Mechanical Keyboard',
        'in_display' => true,
        'stock' => 5,
    ]);

    $this->get('/store?search=gaming')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('querySearch', 'gaming')
            ->has('products.data', 1)
            ->where('products.data.0.id', $match->id)
        );
});

it('filters products by category', function () {
    $category = ProductFixtures::category();
    $other = ProductFixtures::category();

    $match = ProductFixtures::productFor([
        'category_id' => $category->id,
        'in_display' => true,
        'stock' => 5,
    ]);
    ProductFixtures::productFor([
        'category_id' => $other->id,
        'in_display' => true,
        'stock' => 5,
    ]);

    $this->get("/store?category={$category->id}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('queryCategory', (string) $category->id)
            ->has('products.data', 1)
            ->where('products.data.0.id', $match->id)
        );
});

it('lists categories ordered by name', function () {
    ProductCategory::factory()->create(['name' => 'Zeta']);
    ProductCategory::factory()->create(['name' => 'Alpha']);

    $this->get('/store')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('categories', 2)
            ->where('categories.0.name', 'Alpha')
            ->where('categories.1.name', 'Zeta')
        );
});
