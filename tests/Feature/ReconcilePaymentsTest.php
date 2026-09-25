<?php

require_once __DIR__.'/../Support/midtrans_stub.php';

use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Midtrans\Config;
use Midtrans\MT_Tests;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::$serverKey = 'SB-Mid-server-test';
    Config::$clientKey = 'SB-Mid-client-test';
    MT_Tests::$stubHttp = true;
});

afterEach(function () {
    MT_Tests::$stubHttp = false;
    MT_Tests::$stubHttpResponse = null;
    MT_Tests::$lastHttpRequest = [];
});

function seedReconcileTransaction(?string $expiresAt = null): array
{
    $user = User::factory()->create([
        'phone' => '08123',
        'address' => 'Jl. Test 1',
    ]);
    $product = Product::factory()->create(['price' => 100000, 'stock' => 8]);

    $transaction = Transaction::create([
        'user_id' => $user->id,
        'name' => $user->name,
        'phone' => $user->phone,
        'address' => $user->address,
        'total_amount' => 200000,
        'status' => 'pending',
        'expires_at' => $expiresAt ?? now()->addDay(),
    ]);

    $transaction->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'price' => 100000,
        'quantity' => 2,
        'subtotal' => 200000,
    ]);

    return [$transaction, $product];
}

it('closes abandoned transactions and restores stock', function () {
    [$transaction, $product] = seedReconcileTransaction(now()->subHour()->toDateTimeString());

    $this->artisan('payments:reconcile')->assertSuccessful();

    $transaction->refresh();

    expect($transaction->status)->toBe('cancelled');
    expect($transaction->is_complete)->toBeTrue();
    expect($product->refresh()->stock)->toBe(10);
});

it('closes expired transaction even with active payment when gateway still pending', function () {
    [$transaction, $product] = seedReconcileTransaction(now()->subHour()->toDateTimeString());

    $transaction->payments()->create([
        'order_id' => "{$transaction->invoice_number}-1",
        'snap_token' => 'snap-active',
        'status' => Payment::STATUS_ACTIVE,
        'amount' => 200000,
        'expires_at' => now()->addDay(),
    ]);

    MT_Tests::$stubHttpResponse = '{"transaction_status":"pending"}';

    $this->artisan('payments:reconcile')->assertSuccessful();

    $transaction->refresh();

    expect($transaction->status)->toBe('cancelled');
    expect($transaction->is_complete)->toBeTrue();
    expect($product->refresh()->stock)->toBe(10);
});

it('expires stale payment sessions discovered at the gateway', function () {
    [$transaction, $product] = seedReconcileTransaction(now()->addDay());

    $payment = $transaction->payments()->create([
        'order_id' => "{$transaction->invoice_number}-1",
        'snap_token' => 'snap-stale',
        'status' => Payment::STATUS_ACTIVE,
        'amount' => 200000,
        'expires_at' => now()->subHour(),
    ]);

    MT_Tests::$stubHttpResponse = '{"transaction_status":"expire","transaction_id":"mid-stale"}';

    $this->artisan('payments:reconcile')->assertSuccessful();

    expect($payment->refresh()->status)->toBe(Payment::STATUS_EXPIRED);

    $transaction->refresh();

    expect($transaction->status)->toBe('pending');
    expect($transaction->is_complete)->toBeFalse();
    expect($product->refresh()->stock)->toBe(8);
});

it('syncs settlement before closing expired transaction', function () {
    [$transaction, $product] = seedReconcileTransaction(now()->subHour()->toDateTimeString());

    $payment = $transaction->payments()->create([
        'order_id' => "{$transaction->invoice_number}-1",
        'snap_token' => 'snap-active',
        'status' => Payment::STATUS_ACTIVE,
        'amount' => 200000,
        'expires_at' => now()->subHour(),
    ]);

    MT_Tests::$stubHttpResponse = '{"transaction_status":"settlement","payment_type":"bank_transfer","transaction_id":"mid-1"}';

    $this->artisan('payments:reconcile')->assertSuccessful();

    $payment->refresh();
    expect($payment->status)->toBe(Payment::STATUS_PAID);

    $transaction->refresh();
    expect($transaction->status)->toBe('paid');
    expect($transaction->is_complete)->toBeTrue();
    expect($product->refresh()->stock)->toBe(8);
});

it('is idempotent when reconciling expired transaction multiple times', function () {
    [$transaction, $product] = seedReconcileTransaction(now()->subHour()->toDateTimeString());

    MT_Tests::$stubHttpResponse = '{"transaction_status":"pending"}';

    $this->artisan('payments:reconcile')->assertSuccessful();

    $transaction->refresh();
    expect($transaction->status)->toBe('cancelled');
    expect($product->refresh()->stock)->toBe(10);

    $this->artisan('payments:reconcile')->assertSuccessful();

    $transaction->refresh();
    expect($transaction->status)->toBe('cancelled');
    expect($product->refresh()->stock)->toBe(10);
});
