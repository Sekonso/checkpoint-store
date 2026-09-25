<?php

use App\Exceptions\CheckoutException;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedCartForCheckout(User $user, Product $product, int $quantity = 2)
{
    $cart = $user->cart()->create();
    $cart->items()->create([
        'product_id' => $product->id,
        'quantity' => $quantity,
    ]);

    return $cart;
}

it('creates a transaction with profile snapshot and deadline, without a payment session', function () {
    $user = User::factory()->create([
        'name' => 'Budi',
        'phone' => '08123',
        'address' => 'Jl. Test 1',
    ]);
    $product = Product::factory()->create(['price' => 100000, 'stock' => 10]);
    $cart = seedCartForCheckout($user, $product);

    $transaction = app(TransactionService::class)->checkout($user->refresh());
    $transaction->refresh();

    expect($transaction->name)->toBe('Budi');
    expect($transaction->phone)->toBe('08123');
    expect($transaction->address)->toBe('Jl. Test 1');
    expect($transaction->total_amount)->toBe(200000);
    expect($transaction->status)->toBe('pending');
    expect($transaction->is_complete)->toBeFalse();
    expect($transaction->expires_at)->not->toBeNull();
    expect($transaction->payments()->count())->toBe(0);
    expect($transaction->items()->count())->toBe(1);
    expect($product->refresh()->stock)->toBe(8);
    expect($cart->refresh()->items()->count())->toBe(0);
});

it('rejects checkout when phone or address is missing', function () {
    $user = User::factory()->create(['phone' => null, 'address' => null]);
    $product = Product::factory()->create(['price' => 100000, 'stock' => 10]);
    seedCartForCheckout($user, $product);

    expect(fn () => app(TransactionService::class)->checkout($user->refresh()))
        ->toThrow(CheckoutException::class);

    expect(Transaction::count())->toBe(0);
    expect(Payment::count())->toBe(0);
});

it('rejects checkout with an empty cart', function () {
    $user = User::factory()->create(['phone' => '08123', 'address' => 'Jl. Test 1']);

    expect(fn () => app(TransactionService::class)->checkout($user->refresh()))
        ->toThrow(CheckoutException::class);

    expect(Transaction::count())->toBe(0);
});

it('rejects checkout when stock is insufficient', function () {
    $user = User::factory()->create(['phone' => '08123', 'address' => 'Jl. Test 1']);
    $product = Product::factory()->create(['price' => 100000, 'stock' => 1]);
    seedCartForCheckout($user, $product, 2);

    expect(fn () => app(TransactionService::class)->checkout($user->refresh()))
        ->toThrow(CheckoutException::class);

    expect(Transaction::count())->toBe(0);
    expect($product->refresh()->stock)->toBe(1);
});
