<?php

use Illuminate\Support\Facades\Storage;
use Tests\Support\ProductFixtures;
use Tests\Support\UserFixtures;

it('deletes a product with its images and stored files', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $product = ProductFixtures::productFor();

    $product->images()->create(['filename' => 'front.webp', 'order' => 1]);

    Storage::disk('public')->put("products/{$product->id}/front.webp", 'front');

    $this->actingAs($admin)
        ->from('/admin/products')
        ->delete("/admin/products/{$product->id}")
        ->assertRedirect('/admin/products');

    $this->assertModelMissing($product);
    $this->assertDatabaseMissing('product_images', ['product_id' => $product->id]);

    expect(Storage::disk('public')->allFiles("products/{$product->id}"))->toBe([]);
});

it('deletes a product without images', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $product = ProductFixtures::productFor();

    $this->actingAs($admin)
        ->from('/admin/products')
        ->delete("/admin/products/{$product->id}")
        ->assertRedirect('/admin/products');

    $this->assertModelMissing($product);
});

it('returns 404 for an unknown product', function () {
    $this->actingAs(UserFixtures::admin())
        ->delete('/admin/products/999999')
        ->assertNotFound();
});
