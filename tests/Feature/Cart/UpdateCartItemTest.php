<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

it('updates the quantity of a cart item', function () {
    $user = User::factory()->create();
    $cart = Cart::create(['user_id' => $user->id]);
    $product = Product::factory()->create(['stock' => 10]);
    $item = CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->from('/cart')
        ->patch("/cart/items/{$item->id}", ['quantity' => 4])
        ->assertRedirect('/cart')
        ->assertSessionHas('toast');

    expect($item->refresh()->quantity)->toBe(4);
});

it('allows a quantity equal to the available stock', function () {
    $user = User::factory()->create();
    $cart = Cart::create(['user_id' => $user->id]);
    $product = Product::factory()->create(['stock' => 3]);
    $item = CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->patch("/cart/items/{$item->id}", ['quantity' => 3])
        ->assertSessionHasNoErrors();

    expect($item->refresh()->quantity)->toBe(3);
});

it('rejects an invalid quantity', function (mixed $quantity) {
    $user = User::factory()->create();
    $cart = Cart::create(['user_id' => $user->id]);
    $product = Product::factory()->create(['stock' => 10]);
    $item = CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->from('/cart')
        ->patch("/cart/items/{$item->id}", ['quantity' => $quantity])
        ->assertRedirect('/cart')
        ->assertSessionHasErrors('quantity');

    expect($item->refresh()->quantity)->toBe(1);
})->with([
    'zero' => [0],
    'negative' => [-1],
    'not an integer' => ['many'],
]);

it('rejects a quantity above the available stock', function () {
    $user = User::factory()->create();
    $cart = Cart::create(['user_id' => $user->id]);
    $product = Product::factory()->create(['stock' => 2]);
    $item = CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->from('/cart')
        ->patch("/cart/items/{$item->id}", ['quantity' => 3])
        ->assertRedirect('/cart')
        ->assertSessionHasErrors([
            'quantity' => 'The requested quantity exceeds available stock.',
        ]);

    expect($item->refresh()->quantity)->toBe(1);
});

it('returns 404 for another customer cart item', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $otherCart = Cart::create(['user_id' => $other->id]);
    $product = Product::factory()->create(['stock' => 10]);
    $item = CartItem::create([
        'cart_id' => $otherCart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->patch("/cart/items/{$item->id}", ['quantity' => 5])
        ->assertNotFound();

    expect($item->refresh()->quantity)->toBe(1);
});

it('returns 404 for an unknown cart item', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/cart/items/999999', ['quantity' => 2])
        ->assertNotFound();
});
