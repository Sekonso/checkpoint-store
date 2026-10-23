<?php

use App\Models\Product;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\ProductFixtures;
use Tests\Support\UserFixtures;

it('lists the latest products with pagination', function () {
    $admin = UserFixtures::admin();
    $now = now();

    $this->travelTo($now->copy()->subDay());
    $older = ProductFixtures::productFor();

    $this->travelTo($now);
    $newer = ProductFixtures::productFor();

    $this->actingAs($admin)
        ->get('/admin/products')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Products/index')
            ->where('querySearch', null)
            ->where('queryDisplay', null)
            ->has('paginatedProducts.data', 2)
            ->where('paginatedProducts.data.0.id', $newer->id)
            ->where('paginatedProducts.data.1.id', $older->id)
        );
});

it('paginates products fifteen per page', function () {
    $admin = UserFixtures::admin();

    Product::factory()->count(16)->create([
        'category_id' => ProductFixtures::category()->id,
    ]);

    $this->actingAs($admin)
        ->get('/admin/products')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('paginatedProducts.data', 15)
            ->where('paginatedProducts.total', 16)
        );
});

it('filters products by a case-insensitive name search', function () {
    $admin = UserFixtures::admin();

    $match = ProductFixtures::productFor(['name' => 'Gaming Mouse']);
    ProductFixtures::productFor(['name' => 'Mechanical Keyboard']);

    $this->actingAs($admin)
        ->get('/admin/products?search=gaming')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('querySearch', 'gaming')
            ->has('paginatedProducts.data', 1)
            ->where('paginatedProducts.data.0.id', $match->id)
        );
});

it('filters products by display status', function (string $display, bool $inDisplay) {
    $admin = UserFixtures::admin();

    $displayed = ProductFixtures::productFor(['in_display' => true]);
    $hidden = ProductFixtures::productFor(['in_display' => false]);

    $expected = $inDisplay ? $displayed : $hidden;

    $this->actingAs($admin)
        ->get("/admin/products?display={$display}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('queryDisplay', $display)
            ->has('paginatedProducts.data', 1)
            ->where('paginatedProducts.data.0.id', $expected->id)
        );
})->with([
    'displayed' => ['true', true],
    'non-displayed' => ['false', false],
]);

it('ignores an invalid display filter', function () {
    $admin = UserFixtures::admin();

    ProductFixtures::productFor(['in_display' => true]);
    ProductFixtures::productFor(['in_display' => false]);

    $this->actingAs($admin)
        ->get('/admin/products?display=unknown')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('queryDisplay', 'unknown')
            ->has('paginatedProducts.data', 2)
        );
});
