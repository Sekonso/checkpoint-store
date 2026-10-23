<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;

it('creates a pending transaction from the cart', function () {
    $user = User::factory()->create([
        'phone' => '081234567890',
        'address' => '42 Checkpoint Avenue',
    ]);
    $cart = Cart::create(['user_id' => $user->id]);

    $first = Product::factory()->create([
        'price' => 150000,
        'stock' => 10,
    ]);
    $second = Product::factory()->create([
        'price' => 75000,
        'stock' => 5,
    ]);

    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $first->id,
        'quantity' => 2,
    ]);
    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $second->id,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($user)->post('/transactions');

    $transaction = Transaction::sole();

    $response->assertRedirect("/transactions/{$transaction->invoice_number}");

    expect($transaction->user_id)->toBe($user->id)
        ->and($transaction->name)->toBe($user->name)
        ->and($transaction->phone)->toBe('081234567890')
        ->and($transaction->address)->toBe('42 Checkpoint Avenue')
        ->and($transaction->total_amount)->toBe(375000)
        ->and($transaction->status)->toBe(Transaction::STATUS_PENDING)
        ->and($transaction->is_complete)->toBeFalse();

    $this->assertDatabaseHas('transaction_items', [
        'transaction_id' => $transaction->id,
        'product_id' => $first->id,
        'product_name' => $first->name,
        'price' => 150000,
        'quantity' => 2,
        'subtotal' => 300000,
    ]);
    $this->assertDatabaseHas('transaction_items', [
        'transaction_id' => $transaction->id,
        'product_id' => $second->id,
        'product_name' => $second->name,
        'price' => 75000,
        'quantity' => 1,
        'subtotal' => 75000,
    ]);

    expect($first->refresh()->stock)->toBe(8)
        ->and($second->refresh()->stock)->toBe(4)
        ->and($cart->items()->count())->toBe(0);
});

it('rejects checkout when the cart is empty', function () {
    $user = User::factory()->create([
        'phone' => '081234567890',
        'address' => '42 Checkpoint Avenue',
    ]);

    $this->actingAs($user)
        ->from('/cart')
        ->post('/transactions')
        ->assertRedirect('/cart')
        ->assertSessionHas('form_error', 'Your cart is empty.');

    $this->assertDatabaseCount('transactions', 0);
});

it('rejects checkout without a complete profile', function (?string $phone, ?string $address) {
    $user = User::factory()->create([
        'phone' => $phone,
        'address' => $address,
    ]);
    $cart = Cart::create(['user_id' => $user->id]);
    $product = Product::factory()->create(['stock' => 5]);
    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->from('/cart')
        ->post('/transactions')
        ->assertRedirect('/cart')
        ->assertSessionHas(
            'form_error',
            'Please fill out phone and address on the profile before checking out.',
        );

    $this->assertDatabaseCount('transactions', 0);
    expect($product->refresh()->stock)->toBe(5)
        ->and($cart->items()->count())->toBe(1);
})->with([
    'missing phone' => [null, '42 Checkpoint Avenue'],
    'missing address' => ['081234567890', null],
]);

it('rejects checkout when a cart quantity exceeds stock', function () {
    $user = User::factory()->create([
        'phone' => '081234567890',
        'address' => '42 Checkpoint Avenue',
    ]);
    $cart = Cart::create(['user_id' => $user->id]);
    $product = Product::factory()->create(['stock' => 1]);
    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $this->actingAs($user)
        ->from('/cart')
        ->post('/transactions')
        ->assertRedirect('/cart')
        ->assertSessionHas(
            'form_error',
            "Stock for {$product->name} is not available.",
        );

    $this->assertDatabaseCount('transactions', 0);
    expect($product->refresh()->stock)->toBe(1)
        ->and($cart->items()->count())->toBe(1);
});
