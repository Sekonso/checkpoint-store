<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

it('removes every item from the customer cart', function () {
    $user = User::factory()->create();
    $cart = Cart::create(['user_id' => $user->id]);
    $firstProduct = Product::factory()->create();
    $secondProduct = Product::factory()->create();

    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $firstProduct->id,
        'quantity' => 1,
    ]);
    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $secondProduct->id,
        'quantity' => 3,
    ]);

    $this->actingAs($user)
        ->from('/cart')
        ->delete('/cart')
        ->assertRedirect('/cart')
        ->assertSessionHas('toast');

    $this->assertModelExists($cart);
    $this->assertDatabaseCount('cart_items', 0);
});

it('does not clear another customer cart', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $cart = Cart::create(['user_id' => $user->id]);
    $otherCart = Cart::create(['user_id' => $other->id]);
    $product = Product::factory()->create();
    $otherProduct = Product::factory()->create();

    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);
    $otherItem = CartItem::create([
        'cart_id' => $otherCart->id,
        'product_id' => $otherProduct->id,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->delete('/cart')
        ->assertSessionHas('toast');

    expect($cart->items()->count())->toBe(0);
    $this->assertModelExists($otherItem);
});

it('creates a cart when the customer has none', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/cart')
        ->delete('/cart')
        ->assertRedirect('/cart')
        ->assertSessionHas('toast');

    $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
});
