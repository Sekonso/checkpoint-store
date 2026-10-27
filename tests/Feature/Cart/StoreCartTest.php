<?php

use App\Models\Product;
use App\Models\User;

it('adds a displayed in-stock product to the cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->display()->create(['stock' => 5]);

    $this->actingAs($user)
        ->from("/store/{$product->slug}")
        ->post('/cart', ['product_id' => $product->id])
        ->assertRedirect("/store/{$product->slug}")
        ->assertSessionHasNoErrors();

    $cart = $user->cart()->firstOrFail();

    expect($cart->items)->toHaveCount(1)
        ->and($cart->items->first()->product_id)->toBe($product->id)
        ->and($cart->items->first()->quantity)->toBe(1);
});

it('increments the quantity when the same product is added again', function () {
    $user = User::factory()->create();
    $product = Product::factory()->display()->create(['stock' => 5]);

    $this->actingAs($user)->post('/cart', ['product_id' => $product->id]);
    $this->actingAs($user)->post('/cart', ['product_id' => $product->id]);

    $cart = $user->cart()->firstOrFail();

    expect($cart->items)->toHaveCount(1)
        ->and($cart->items->first()->quantity)->toBe(2);
});

it('adds separate lines for distinct products', function () {
    $user = User::factory()->create();
    $first = Product::factory()->display()->create(['stock' => 5]);
    $second = Product::factory()->display()->create(['stock' => 5]);

    $this->actingAs($user)->post('/cart', ['product_id' => $first->id]);
    $this->actingAs($user)->post('/cart', ['product_id' => $second->id]);

    $cart = $user->cart()->firstOrFail();

    expect($cart->items)->toHaveCount(2)
        ->and($cart->items->pluck('product_id')->all())->toEqualCanonicalizing([
            $first->id,
            $second->id,
        ]);
});

it('rejects a product that is not displayed', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'in_display' => false,
        'stock' => 5,
    ]);

    $this->actingAs($user)
        ->from("/store/{$product->slug}")
        ->post('/cart', ['product_id' => $product->id])
        ->assertRedirect("/store/{$product->slug}")
        ->assertSessionHasErrors([
            'product_id' => 'This product is not available.',
        ]);

    $this->assertDatabaseCount('carts', 0);
    $this->assertDatabaseCount('cart_items', 0);
});

it('rejects a product that is out of stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->display()->create(['stock' => 0]);

    $this->actingAs($user)
        ->from("/store/{$product->slug}")
        ->post('/cart', ['product_id' => $product->id])
        ->assertRedirect("/store/{$product->slug}")
        ->assertSessionHasErrors([
            'product_id' => 'This product is not available.',
        ]);

    $this->assertDatabaseCount('carts', 0);
    $this->assertDatabaseCount('cart_items', 0);
});

it('rejects adding more than the available stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->display()->create(['stock' => 1]);

    $this->actingAs($user)->post('/cart', ['product_id' => $product->id]);

    $this->actingAs($user)
        ->from("/store/{$product->slug}")
        ->post('/cart', ['product_id' => $product->id])
        ->assertRedirect("/store/{$product->slug}")
        ->assertSessionHasErrors([
            'quantity' => 'The requested quantity exceeds available stock.',
        ]);

    $cart = $user->cart()->firstOrFail();

    expect($cart->items)->toHaveCount(1)
        ->and($cart->items->first()->quantity)->toBe(1);
});

it('requires a product id', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/store')
        ->post('/cart', [])
        ->assertRedirect('/store')
        ->assertSessionHasErrors('product_id');

    $this->assertDatabaseCount('carts', 0);
    $this->assertDatabaseCount('cart_items', 0);
});

it('rejects an unknown product', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/store')
        ->post('/cart', ['product_id' => 999999])
        ->assertRedirect('/store')
        ->assertSessionHasErrors('product_id');

    $this->assertDatabaseCount('carts', 0);
    $this->assertDatabaseCount('cart_items', 0);
});
