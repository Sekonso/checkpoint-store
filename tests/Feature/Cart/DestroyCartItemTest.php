<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

it('removes a cart item and leaves the other items intact', function () {
    $user = User::factory()->create();
    $cart = Cart::create(['user_id' => $user->id]);
    $firstProduct = Product::factory()->create();
    $secondProduct = Product::factory()->create();

    $first = CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $firstProduct->id,
        'quantity' => 1,
    ]);
    $second = CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $secondProduct->id,
        'quantity' => 2,
    ]);

    $this->actingAs($user)
        ->from('/cart')
        ->delete("/cart/items/{$first->id}")
        ->assertRedirect('/cart')
        ->assertSessionHas('toast');

    $this->assertModelMissing($first);
    $this->assertModelExists($second);
});

it('returns 404 for another customer cart item', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $otherCart = Cart::create(['user_id' => $other->id]);
    $product = Product::factory()->create();
    $item = CartItem::create([
        'cart_id' => $otherCart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->delete("/cart/items/{$item->id}")
        ->assertNotFound();

    $this->assertModelExists($item);
});

it('returns 404 for an unknown cart item', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete('/cart/items/999999')
        ->assertNotFound();
});
