<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the customer cart items newest first', function () {
    $user = User::factory()->create();
    $cart = Cart::create(['user_id' => $user->id]);
    $now = now();

    $this->travelTo($now->copy()->subDay());
    $olderProduct = Product::factory()->create();
    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $olderProduct->id,
        'quantity' => 1,
    ]);

    $this->travelTo($now);
    $newerProduct = Product::factory()->create();
    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $newerProduct->id,
        'quantity' => 2,
    ]);

    $this->actingAs($user)
        ->get('/cart')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Cart/index')
            ->where('cart.id', $cart->id)
            ->has('cart.items', 2)
            ->where('cart.items.0.product.id', $newerProduct->id)
            ->where('cart.items.1.product.id', $olderProduct->id)
        );
});

it('creates an empty cart when the customer has none', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/cart')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Cart/index')
            ->where('cart.user_id', $user->id)
            ->has('cart.items', 0)
        );

    $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
});

it('shows only the authenticated customer cart', function () {
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
    CartItem::create([
        'cart_id' => $otherCart->id,
        'product_id' => $otherProduct->id,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->get('/cart')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cart.id', $cart->id)
            ->has('cart.items', 1)
            ->where('cart.items.0.product.id', $product->id)
        );
});
