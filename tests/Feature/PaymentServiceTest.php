<?php

require_once __DIR__.'/../Support/midtrans_stub.php';

use App\Exceptions\PaymentAlreadySettledException;
use App\Exceptions\PaymentException;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentService;
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

function seedTransactionForPayment(): Transaction
{
    $user = User::factory()->create([
        'name' => 'Budi',
        'phone' => '08123',
        'address' => 'Jl. Test 1',
    ]);

    $product = Product::factory()->create(['price' => 100000, 'stock' => 10]);

    $transaction = Transaction::create([
        'user_id' => $user->id,
        'name' => $user->name,
        'phone' => $user->phone,
        'address' => $user->address,
        'total_amount' => 200000,
        'status' => 'pending',
        'expires_at' => now()->addDay(),
    ]);

    $transaction->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'price' => 100000,
        'quantity' => 2,
        'subtotal' => 200000,
    ]);

    return $transaction;
}

it('creates a payment session with snap expiry and customer snapshot', function () {
    $transaction = seedTransactionForPayment();

    MT_Tests::$stubHttpResponse = '{"token":"snap-test-123"}';

    $deadline = $transaction->expires_at;

    $payment = app(PaymentService::class)->createPayment($transaction);

    expect($payment->order_id)->toBe("{$transaction->invoice_number}-1");
    expect($payment->snap_token)->toBe('snap-test-123')
        ->and($payment->status)->toBe(Payment::STATUS_ACTIVE)
        ->and($payment->amount)->toBe(200000)
        ->and($payment->expires_at->eq($deadline))->toBeTrue()
        ->and($transaction->refresh()->expires_at->eq($deadline))->toBeTrue();

    $request = MT_Tests::$lastHttpRequest;

    expect($request['url'])->toBe('https://app.sandbox.midtrans.com/snap/v1/transactions');

    $hash = $request['data_hash'];

    expect($hash['transaction_details']['order_id'])->toBe("{$transaction->invoice_number}-1");
    expect($hash['transaction_details']['gross_amount'])->toBe(200000);
    expect($hash['expiry']['unit'])->toBe('minute');
    expect($hash['expiry']['duration'])->toBeGreaterThan(1400)->toBeLessThanOrEqual(1440);
    expect($hash['customer_details']['first_name'])->toBe('Budi');
    expect($hash['customer_details']['phone'])->toBe('08123');
});

it('reuses the active payment without hitting the API again', function () {
    $transaction = seedTransactionForPayment();

    $transaction->payments()->create([
        'order_id' => "{$transaction->invoice_number}-1",
        'snap_token' => 'stored-token',
        'status' => Payment::STATUS_ACTIVE,
        'amount' => 200000,
    ]);

    MT_Tests::$stubHttpResponse = '{"token":"would-be-overwritten"}';

    $payment = app(PaymentService::class)->createPayment($transaction);

    expect($payment->snap_token)->toBe('stored-token');
    expect($transaction->payments()->count())->toBe(1);
    expect(MT_Tests::$lastHttpRequest)->toBe([]);
});

it('caps the payment session at the transaction deadline', function () {
    $transaction = seedTransactionForPayment();
    $transaction->update(['expires_at' => now()->addHours(2)]);

    $deadline = $transaction->refresh()->expires_at;

    MT_Tests::$stubHttpResponse = '{"token":"snap-short"}';

    $payment = app(PaymentService::class)->createPayment($transaction);

    expect($payment->expires_at->eq($deadline))->toBeTrue();
    expect($transaction->refresh()->expires_at->eq($deadline))->toBeTrue();

    $hash = MT_Tests::$lastHttpRequest['data_hash'];

    expect($hash['expiry']['unit'])->toBe('minute');
    expect($hash['expiry']['duration'])->toBeGreaterThan(100)->toBeLessThanOrEqual(120);
});

it('resets a session by retiring the old row and creating a new one', function () {
    $transaction = seedTransactionForPayment();

    $old = $transaction->payments()->create([
        'order_id' => "{$transaction->invoice_number}-1",
        'snap_token' => 'snap-old',
        'status' => Payment::STATUS_ACTIVE,
        'amount' => 200000,
    ]);

    MT_Tests::$stubHttpResponse = '{"token":"snap-new","transaction_status":"pending","transaction_id":null}';

    $deadline = $transaction->expires_at;

    $payment = app(PaymentService::class)->resetPayment($transaction);

    expect($payment->order_id)->toBe("{$transaction->invoice_number}-2");
    expect($payment->snap_token)->toBe('snap-new');
    expect($payment->expires_at->eq($deadline))->toBeTrue();
    expect($transaction->refresh()->expires_at->eq($deadline))->toBeTrue();
    expect($payment->status)->toBe(Payment::STATUS_ACTIVE);
    expect($old->refresh()->status)->toBe(Payment::STATUS_CANCELLED);
    expect($transaction->payments()->count())->toBe(2);

    $transaction->refresh();

    expect($transaction->status)->toBe('pending');
    expect($transaction->is_complete)->toBeFalse();
});

it('applies settlement instead of resetting when already paid at gateway', function () {
    $transaction = seedTransactionForPayment();

    $transaction->payments()->create([
        'order_id' => "{$transaction->invoice_number}-1",
        'snap_token' => 'snap-old',
        'status' => Payment::STATUS_ACTIVE,
        'amount' => 200000,
    ]);

    MT_Tests::$stubHttpResponse = '{"transaction_status":"settlement","payment_type":"bank_transfer","transaction_id":"mid-1","settlement_time":"2026-09-23 10:00:00"}';

    expect(fn () => app(PaymentService::class)->resetPayment($transaction))
        ->toThrow(PaymentAlreadySettledException::class);

    $transaction->refresh();

    expect($transaction->status)->toBe('paid');
    expect($transaction->is_complete)->toBeTrue();
    expect($transaction->payment_type)->toBe('bank_transfer');
});

it('applies late settlement after transaction was cancelled', function () {
    $transaction = seedTransactionForPayment();
    $product = $transaction->items()->first()->product;

    $payment = $transaction->payments()->create([
        'order_id' => "{$transaction->invoice_number}-1",
        'snap_token' => 'snap-old',
        'status' => Payment::STATUS_ACTIVE,
        'amount' => 200000,
    ]);

    $transaction->markCancelled();
    $product->increment('stock', 2);
    $product->refresh();

    MT_Tests::$stubHttpResponse = '{"transaction_status":"settlement","payment_type":"qris","transaction_id":"mid-late","settlement_time":"2026-09-24 10:00:00"}';

    app(PaymentService::class)->applyGatewayStatus($payment, 'settlement', [
        'payment_type' => 'qris',
        'transaction_id' => 'mid-late',
        'paid_at' => '2026-09-24 10:00:00',
    ]);

    $transaction->refresh();
    expect($transaction->status)->toBe('paid');
    expect($transaction->is_complete)->toBeTrue();
    expect($transaction->payment_type)->toBe('qris');

    $payment->refresh();
    expect($payment->status)->toBe(Payment::STATUS_PAID);
    expect($payment->midtrans_transaction_id)->toBe('mid-late');

    expect($product->refresh()->stock)->toBe(10);
});

it('does not change stock on repeated settlement after late settlement', function () {
    $transaction = seedTransactionForPayment();
    $product = $transaction->items()->first()->product;

    $payment = $transaction->payments()->create([
        'order_id' => "{$transaction->invoice_number}-1",
        'snap_token' => 'snap-old',
        'status' => Payment::STATUS_ACTIVE,
        'amount' => 200000,
    ]);

    $transaction->markCancelled();
    $product->increment('stock', 2);
    $product->refresh();

    MT_Tests::$stubHttpResponse = '{"transaction_status":"settlement","payment_type":"qris","transaction_id":"mid-late"}';

    app(PaymentService::class)->applyGatewayStatus($payment, 'settlement');

    expect($product->refresh()->stock)->toBe(10);

    app(PaymentService::class)->applyGatewayStatus($payment, 'capture');
    expect($product->refresh()->stock)->toBe(10);
});

it('syncs an expired session from the gateway without touching the transaction', function () {
    $transaction = seedTransactionForPayment();
    $product = $transaction->items()->first()->product;

    $payment = $transaction->payments()->create([
        'order_id' => "{$transaction->invoice_number}-1",
        'snap_token' => 'snap-old',
        'status' => Payment::STATUS_ACTIVE,
        'amount' => 200000,
    ]);

    MT_Tests::$stubHttpResponse = '{"transaction_status":"expire","transaction_id":"mid-1"}';

    app(PaymentService::class)->syncFromGateway($transaction);

    expect($payment->refresh()->status)->toBe(Payment::STATUS_EXPIRED);

    $transaction->refresh();

    expect($transaction->status)->toBe('pending');
    expect($transaction->is_complete)->toBeFalse();
    expect($product->refresh()->stock)->toBe(10);
});

it('rejects payment actions on completed transactions', function () {
    $transaction = seedTransactionForPayment();
    $transaction->markCancelled();

    expect(fn () => app(PaymentService::class)->createPayment($transaction))
        ->toThrow(PaymentException::class);
});
