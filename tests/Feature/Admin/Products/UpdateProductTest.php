<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Tests\Support\ProductFixtures;
use Tests\Support\UserFixtures;

it('updates the product fields and slug', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $product = ProductFixtures::productFor([
        'name' => 'Old Name',
        'description' => 'Old description',
        'price' => 50000,
        'stock' => 5,
        'in_display' => false,
    ]);
    $category = ProductFixtures::category();

    $payload = Arr::except(
        ProductFixtures::payload([
            'name' => 'New Name',
            'description' => 'New description',
            'price' => 75000,
            'stock' => 10,
            'in_display' => true,
            'category_id' => $category->id,
        ]),
        ['image-1', 'image-2', 'image-3'],
    );

    $this->actingAs($admin)
        ->from("/admin/products/{$product->id}/edit")
        ->put("/admin/products/{$product->id}", $payload)
        ->assertRedirect("/admin/products/{$product->id}/edit")
        ->assertSessionHas('toast');

    $product->refresh();

    expect($product->name)->toBe('New Name')
        ->and($product->slug)->toBe('new-name')
        ->and($product->description)->toBe('New description')
        ->and($product->price)->toBe(75000)
        ->and($product->stock)->toBe(10)
        ->and((bool) $product->in_display)->toBeTrue()
        ->and($product->category_id)->toBe($category->id);
});

it('keeps the existing images when no new image is uploaded', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $product = ProductFixtures::productFor();

    $product->images()->createMany([
        ['filename' => 'front.webp', 'order' => 1],
        ['filename' => 'side.webp', 'order' => 2],
    ]);

    Storage::disk('public')->put("products/{$product->id}/front.webp", 'front');
    Storage::disk('public')->put("products/{$product->id}/side.webp", 'side');

    $payload = Arr::except(
        ProductFixtures::payload(),
        ['image-1', 'image-2', 'image-3'],
    );

    $this->actingAs($admin)
        ->from("/admin/products/{$product->id}/edit")
        ->put("/admin/products/{$product->id}", $payload)
        ->assertRedirect("/admin/products/{$product->id}/edit");

    $product->refresh();

    expect($product->images)->toHaveCount(2)
        ->and($product->images->pluck('filename')->all())->toEqualCanonicalizing(['front.webp', 'side.webp']);

    Storage::disk('public')->assertExists("products/{$product->id}/front.webp");
    Storage::disk('public')->assertExists("products/{$product->id}/side.webp");
});

it('replaces an existing image while keeping its filename and order', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $product = ProductFixtures::productFor();

    $product->images()->create(['filename' => 'front.webp', 'order' => 1]);

    Storage::disk('public')->put("products/{$product->id}/front.webp", 'old-content');

    $payload = Arr::except(
        ProductFixtures::payload(),
        ['image-2', 'image-3'],
    );

    $this->actingAs($admin)
        ->from("/admin/products/{$product->id}/edit")
        ->put("/admin/products/{$product->id}", $payload)
        ->assertRedirect("/admin/products/{$product->id}/edit");

    $image = $product->images()->where('order', 1)->firstOrFail();

    expect($image->filename)->toBe('front.webp')
        ->and(Storage::disk('public')->get("products/{$product->id}/front.webp"))->not->toBe('old-content')
        ->and($product->images()->count())->toBe(1);
});

it('adds a new image when the slot was empty', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $product = ProductFixtures::productFor();

    $product->images()->create(['filename' => 'front.webp', 'order' => 1]);

    $payload = Arr::except(
        ProductFixtures::payload(),
        ['image-1', 'image-3'],
    );

    $this->actingAs($admin)
        ->from("/admin/products/{$product->id}/edit")
        ->put("/admin/products/{$product->id}", $payload)
        ->assertRedirect("/admin/products/{$product->id}/edit");

    $newImage = $product->images()->where('order', 2)->firstOrFail();

    expect($product->images()->count())->toBe(2)
        ->and($newImage->filename)->not->toBeEmpty();

    Storage::disk('public')->assertExists("products/{$product->id}/{$newImage->filename}");
});

it('allows keeping the current product name', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $product = ProductFixtures::productFor(['name' => 'Same Name']);

    $payload = Arr::except(
        ProductFixtures::payload(['name' => 'Same Name']),
        ['image-1', 'image-2', 'image-3'],
    );

    $this->actingAs($admin)
        ->from("/admin/products/{$product->id}/edit")
        ->put("/admin/products/{$product->id}", $payload)
        ->assertRedirect("/admin/products/{$product->id}/edit")
        ->assertSessionHasNoErrors();
});

it('rejects a name used by another product', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $product = ProductFixtures::productFor(['name' => 'First Name']);

    ProductFixtures::productFor(['name' => 'Second Name']);

    $payload = Arr::except(
        ProductFixtures::payload(['name' => 'Second Name']),
        ['image-1', 'image-2', 'image-3'],
    );

    $this->actingAs($admin)
        ->from("/admin/products/{$product->id}/edit")
        ->put("/admin/products/{$product->id}", $payload)
        ->assertSessionHasErrors('name');
});

it('requires the product fields on update', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $product = ProductFixtures::productFor();

    $this->actingAs($admin)
        ->from("/admin/products/{$product->id}/edit")
        ->put("/admin/products/{$product->id}", [])
        ->assertRedirect("/admin/products/{$product->id}/edit")
        ->assertSessionHasErrors([
            'name',
            'description',
            'category_id',
            'price',
            'stock',
            'in_display',
        ]);
});

it('rejects invalid product attributes on update', function (string $field, mixed $value) {
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $product = ProductFixtures::productFor();

    $payload = Arr::except(
        ProductFixtures::payload([$field => $value]),
        ['image-1', 'image-2', 'image-3'],
    );

    $this->actingAs($admin)
        ->from("/admin/products/{$product->id}/edit")
        ->put("/admin/products/{$product->id}", $payload)
        ->assertSessionHasErrors($field);
})->with([
    'price below the minimum' => ['price', 999],
    'unknown category' => ['category_id', 999999],
    'non-boolean display value' => ['in_display', 'maybe'],
]);

it('rejects a non-image file on update', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $product = ProductFixtures::productFor();
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $payload = Arr::except(
        ProductFixtures::payload(['image-1' => $file]),
        ['image-2', 'image-3'],
    );

    $this->actingAs($admin)
        ->from("/admin/products/{$product->id}/edit")
        ->put("/admin/products/{$product->id}", $payload)
        ->assertSessionHasErrors('image-1');
});

it('returns 404 for an unknown product', function () {
    Storage::fake('public');

    $this->actingAs(UserFixtures::admin())
        ->put('/admin/products/999999', ProductFixtures::payload())
        ->assertNotFound();
});
