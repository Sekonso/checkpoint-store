<?php

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Support\ProductFixtures;
use Tests\Support\UserFixtures;

it('creates a product with its images', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $category = ProductFixtures::category();
    $payload = ProductFixtures::payload([
        'name' => 'Gaming Mouse',
        'category_id' => $category->id,
        'in_display' => true,
    ]);

    $this->actingAs($admin)
        ->post('/admin/products', $payload)
        ->assertRedirect('/admin/products')
        ->assertSessionHas('toast');

    $product = Product::query()->where('name', 'Gaming Mouse')->firstOrFail();

    expect($product->slug)->toBe(Str::slug('Gaming Mouse'))
        ->and($product->category_id)->toBe($category->id)
        ->and($product->description)->toBe($payload['description'])
        ->and($product->price)->toBe($payload['price'])
        ->and($product->stock)->toBe($payload['stock'])
        ->and((bool) $product->in_display)->toBeTrue()
        ->and($product->images)->toHaveCount(3)
        ->and($product->images->pluck('order')->all())->toEqualCanonicalizing([1, 2, 3]);

    foreach ($product->images as $image) {
        Storage::disk('public')->assertExists("products/{$product->id}/{$image->filename}");
    }
});

it('generates a slug from the product name', function () {
    Storage::fake('public');

    $this->actingAs(UserFixtures::admin())
        ->post('/admin/products', ProductFixtures::payload(['name' => 'Wireless Gaming Mouse!']))
        ->assertRedirect('/admin/products');

    $product = Product::query()->where('name', 'Wireless Gaming Mouse!')->firstOrFail();

    expect($product->slug)->toBe('wireless-gaming-mouse');
});

it('requires the product fields', function () {
    $this->actingAs(UserFixtures::admin())
        ->from('/admin/products/create')
        ->post('/admin/products', [])
        ->assertRedirect('/admin/products/create')
        ->assertSessionHasErrors([
            'name',
            'description',
            'category_id',
            'price',
            'stock',
            'in_display',
            'image-1',
            'image-2',
            'image-3',
        ]);
});

it('rejects a duplicate product name', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();

    ProductFixtures::productFor(['name' => 'Duplicate Name']);

    $this->actingAs($admin)
        ->from('/admin/products/create')
        ->post('/admin/products', ProductFixtures::payload(['name' => 'Duplicate Name']))
        ->assertSessionHasErrors('name');
});

it('rejects an unknown category', function () {
    Storage::fake('public');

    $this->actingAs(UserFixtures::admin())
        ->from('/admin/products/create')
        ->post('/admin/products', ProductFixtures::payload(['category_id' => 999999]))
        ->assertSessionHasErrors('category_id');
});

it('rejects invalid product attributes', function (string $field, mixed $value) {
    Storage::fake('public');

    $this->actingAs(UserFixtures::admin())
        ->from('/admin/products/create')
        ->post('/admin/products', ProductFixtures::payload([$field => $value]))
        ->assertSessionHasErrors($field);
})->with([
    'price below the minimum' => ['price', 999],
    'negative stock' => ['stock', -1],
    'non-boolean display value' => ['in_display', 'maybe'],
]);

it('rejects an image that is not an image', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $this->actingAs(UserFixtures::admin())
        ->from('/admin/products/create')
        ->post('/admin/products', ProductFixtures::payload(['image-1' => $file]))
        ->assertSessionHasErrors('image-1');
});

it('rejects an image larger than two megabytes', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('large.jpg')->size(3000);

    $this->actingAs(UserFixtures::admin())
        ->from('/admin/products/create')
        ->post('/admin/products', ProductFixtures::payload(['image-1' => $file]))
        ->assertSessionHasErrors('image-1');
});
