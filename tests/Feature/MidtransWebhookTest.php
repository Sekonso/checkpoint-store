<?php

use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedWebhookTransaction(): array
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
    ]);

    $transaction->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'price' => 100000,
        'quantity' => 2,
        'subtotal' => 200000,
    ]);

    $payment = $transaction->payments()->create([
        'order_id' => "{$transaction->invoice_number}-1",
        'snap_token' => 'snap-test',
        'status' => Payment::STATUS_ACTIVE,
        'amount' => 200000,
    ]);

    return [$transaction, $product, $payment];
}

it('maps Midtrans statuses', function () {
    expect(Transaction::mapMidtransStatus('settlement'))->toBe('paid');
    expect(Transaction::mapMidtransStatus('capture'))->toBe('paid');
    expect(Transaction::mapMidtransStatus('pending'))->toBe('pending');
    expect(Transaction::mapMidtransStatus('cancel'))->toBe('cancelled');
    expect(Transaction::mapMidtransStatus('deny'))->toBe('cancelled');
    expect(Transaction::mapMidtransStatus('expire'))->toBe('cancelled');
    expect(Transaction::mapMidtransStatus('refund'))->toBeNull();
});

it('marks a pending transaction as paid with metadata', function () {
    [$transaction, $product, $payment] = seedWebhookTransaction();

    app(PaymentService::class)->applyGatewayStatus($payment, 'settlement', [
        'payment_type' => 'bank_transfer',
        'transaction_id' => 'mid-123',
        'paid_at' => '2026-09-20 10:00:00',
    ]);

    $transaction->refresh();

    expect($transaction->status)->toBe('paid');
    expect($transaction->is_complete)->toBeTrue();
    expect($transaction->payment_type)->toBe('bank_transfer');
    expect($transaction->paid_at->format('Y-m-d H:i:s'))->toBe('2026-09-20 10:00:00');
    expect($product->refresh()->stock)->toBe(10);

    $payment->refresh();

    expect($payment->status)->toBe(Payment::STATUS_PAID);
    expect($payment->method)->toBe('bank_transfer');
    expect($payment->midtrans_transaction_id)->toBe('mid-123');
});

it('retires the payment row on expiry without touching the transaction', function (string $midtransStatus) {
    [$transaction, $product, $payment] = seedWebhookTransaction();

    app(PaymentService::class)->applyGatewayStatus($payment, $midtransStatus, [
        'transaction_id' => 'mid-123',
    ]);

    $transaction->refresh();

    expect($transaction->status)->toBe('pending');
    expect($transaction->is_complete)->toBeFalse();
    expect($transaction->paid_at)->toBeNull();
    expect($product->refresh()->stock)->toBe(10);
    expect($payment->refresh()->midtrans_transaction_id)->toBe('mid-123');
})->with([
    'expire' => 'expire',
    'cancelled row' => 'cancel',
    'denied row' => 'deny',
]);

it('settles the transaction on late payment for a retired session', function () {
    [$transaction, $product, $payment] = seedWebhookTransaction();

    $retired = $transaction->payments()->create([
        'order_id' => "{$transaction->invoice_number}-2",
        'snap_token' => 'snap-old',
        'status' => Payment::STATUS_CANCELLED,
        'amount' => 200000,
        'midtrans_transaction_id' => 'mid-old',
    ]);

    app(PaymentService::class)->applyGatewayStatus($retired, 'settlement', [
        'payment_type' => 'qris',
        'transaction_id' => 'mid-old',
        'paid_at' => '2026-09-20 10:00:00',
    ]);

    // Late settlement on a retired session still settles the transaction
    // (money was taken), while row states stay truthful.
    expect($transaction->refresh()->status)->toBe('paid');
    expect($transaction->refresh()->is_complete)->toBeTrue();
    expect($retired->refresh()->status)->toBe(Payment::STATUS_CANCELLED);
    expect($payment->refresh()->status)->toBe(Payment::STATUS_ACTIVE);
});

it('ignores unknown Midtrans statuses', function () {
    [$transaction, $product, $payment] = seedWebhookTransaction();

    app(PaymentService::class)->applyGatewayStatus($payment, 'refund');

    $transaction->refresh();

    expect($transaction->status)->toBe('pending');
    expect($transaction->paid_at)->toBeNull();
    expect($product->refresh()->stock)->toBe(10);
    expect($payment->refresh()->status)->toBe(Payment::STATUS_ACTIVE);
});

it('does nothing for pending notifications', function () {
    [$transaction, $product, $payment] = seedWebhookTransaction();

    app(PaymentService::class)->applyGatewayStatus($payment, 'pending');

    $transaction->refresh();

    expect($transaction->status)->toBe('pending');
    expect($transaction->paid_at)->toBeNull();
    expect($product->refresh()->stock)->toBe(10);
    expect($payment->refresh()->status)->toBe(Payment::STATUS_ACTIVE);
});

it('is idempotent for repeated paid notifications', function () {
    [$transaction, $product, $payment] = seedWebhookTransaction();

    $service = app(PaymentService::class);

    $service->applyGatewayStatus($payment, 'settlement');

    $transaction->refresh();
    $paidAt = $transaction->paid_at;

    $service->applyGatewayStatus($payment, 'capture', [
        'payment_type' => 'qris',
        'paid_at' => '2026-10-01 10:00:00',
    ]);

    $transaction->refresh();

    expect($transaction->status)->toBe('paid');
    expect($transaction->payment_type)->toBeNull();
    expect($transaction->paid_at->eq($paidAt))->toBeTrue();
    expect($product->refresh()->stock)->toBe(10);
    expect($payment->refresh()->status)->toBe(Payment::STATUS_PAID);
});

it('settles transaction when webhook arrives after reconciliation cancelled it', function () {
    [$transaction, $product, $payment] = seedWebhookTransaction();

    $transaction->markCancelled();
    $transaction->refresh();
    $product->increment('stock', 2);
    $product->refresh();

    app(PaymentService::class)->applyGatewayStatus($payment, 'settlement', [
        'payment_type' => 'qris',
        'transaction_id' => 'mid-webhook',
        'paid_at' => '2026-09-24 12:00:00',
    ]);

    $transaction->refresh();
    expect($transaction->status)->toBe('paid');
    expect($transaction->is_complete)->toBeTrue();
    expect($transaction->payment_type)->toBe('qris');

    $payment->refresh();
    expect($payment->status)->toBe(Payment::STATUS_PAID);

    expect($product->refresh()->stock)->toBe(10);
});

it('verifies the Midtrans signature', function () {
    config(['midtrans.server_key' => 'server-key-123']);

    $service = app(PaymentService::class);

    $data = [
        'order_id' => 'INV-20260923-ABCDEF',
        'status_code' => '200',
        'gross_amount' => '200000',
    ];
    $data['signature_key'] = hash('sha512', 'INV-20260923-ABCDEF200200000server-key-123');

    expect($service->verifySignature($data))->toBeTrue();
    expect($service->verifySignature([...$data, 'signature_key' => 'tampered']))->toBeFalse();
    expect($service->verifySignature([]))->toBeFalse();
});

it('registers the Midtrans webhook route', function () {
    $this->get('/webhook/midtrans/notification')->assertStatus(405);
    $this->post('/payment/notification')->assertStatus(404);
});
