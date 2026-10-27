<?php

use App\Models\Payment;
use App\Models\Transaction;
use App\Services\PaymentService;
use Mockery\MockInterface;

use function Pest\Laravel\mock;

function midtransNotificationFor(Payment $payment, array $overrides = []): array
{
    return array_merge([
        'order_id' => $payment->order_id,
        'status' => 'settlement',
        'payment_type' => 'qris',
        'transaction_id' => 'midtrans-txn-123',
        'settlement_time' => '2026-10-25 12:00:00',
        'gross_amount' => number_format($payment->amount, 2, '.', ''),
        'status_code' => '200',
        'signature_key' => 'valid-signature',
    ], $overrides);
}

function mockMidtransNotification(
    array $notification,
    bool $signatureIsValid = true,
): void {
    config(['midtrans.server_key' => 'webhook-test-server-key']);

    $notification['signature_key'] = $signatureIsValid
        ? hash('sha512', implode('', [
            $notification['order_id'] ?? '',
            $notification['status_code'] ?? '',
            $notification['gross_amount'] ?? '',
            'webhook-test-server-key',
        ]))
        : 'invalid-signature';

    mock(PaymentService::class, function (MockInterface $payment) use ($notification) {
        $payment->makePartial();
        $payment->shouldReceive('readNotification')->once()->andReturn($notification);
    });
}

it('accepts a valid settlement and marks its payment and transaction paid', function () {
    $transaction = Transaction::factory()->withAmount(125000)->withActivePayment()->create();
    $payment = $transaction->payments()->firstOrFail();
    $notification = midtransNotificationFor($payment);
    mockMidtransNotification($notification);

    $this->post('/webhook/midtrans/notification')->assertSuccessful();

    $this->assertDatabaseHas('payments', [
        'id' => $payment->id,
        'status' => Payment::STATUS_PAID,
        'method' => 'qris',
        'midtrans_transaction_id' => 'midtrans-txn-123',
    ]);
    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'status' => Transaction::STATUS_PAID,
        'is_complete' => true,
        'payment_type' => 'qris',
    ]);
    expect($payment->refresh()->paid_at->toDateTimeString())->toBe('2026-10-25 12:00:00');
});

it('rejects a notification with an invalid signature without changing payment state', function () {
    $transaction = Transaction::factory()->withAmount(125000)->withActivePayment()->create();
    $payment = $transaction->payments()->firstOrFail();
    mockMidtransNotification(midtransNotificationFor($payment), false);

    $this->post('/webhook/midtrans/notification')->assertForbidden();

    expect($payment->refresh()->status)->toBe(Payment::STATUS_ACTIVE);
    expect($transaction->refresh()->status)->toBe(Transaction::STATUS_PENDING);
});

it('returns not found for an unknown order id without changing an existing payment', function () {
    $transaction = Transaction::factory()->withAmount(125000)->withActivePayment()->create();
    $payment = $transaction->payments()->firstOrFail();
    mockMidtransNotification(midtransNotificationFor($payment, ['order_id' => 'UNKNOWN-ORDER']));

    $this->post('/webhook/midtrans/notification')->assertNotFound();

    expect($payment->refresh()->status)->toBe(Payment::STATUS_ACTIVE);
    expect($transaction->refresh()->status)->toBe(Transaction::STATUS_PENDING);
});

it('rejects a mismatched notification amount without changing payment state', function () {
    $transaction = Transaction::factory()->withAmount(125000)->withActivePayment()->create();
    $payment = $transaction->payments()->firstOrFail();
    mockMidtransNotification(midtransNotificationFor($payment, ['gross_amount' => '125001']));

    $this->post('/webhook/midtrans/notification')->assertUnprocessable();

    expect($payment->refresh()->status)->toBe(Payment::STATUS_ACTIVE);
    expect($transaction->refresh()->status)->toBe(Transaction::STATUS_PENDING);
});

it('rejects a decimal notification amount that exceeds the payment amount', function () {
    $transaction = Transaction::factory()->withAmount(125000)->withActivePayment()->create();
    $payment = $transaction->payments()->firstOrFail();
    mockMidtransNotification(midtransNotificationFor($payment, ['gross_amount' => '125000.99']));

    $this->post('/webhook/midtrans/notification')->assertUnprocessable();

    expect($payment->refresh()->status)->toBe(Payment::STATUS_ACTIVE);
    expect($transaction->refresh()->status)->toBe(Transaction::STATUS_PENDING);
});

it('returns a bad request for an incomplete notification without changing payment state', function () {
    $transaction = Transaction::factory()->withAmount(125000)->withActivePayment()->create();
    $payment = $transaction->payments()->firstOrFail();
    $notification = midtransNotificationFor($payment);
    unset($notification['status']);
    mockMidtransNotification($notification);

    $this->post('/webhook/midtrans/notification')->assertBadRequest();

    expect($payment->refresh()->status)->toBe(Payment::STATUS_ACTIVE);
    expect($transaction->refresh()->status)->toBe(Transaction::STATUS_PENDING);
});

it('returns a bad request when the notification has no order id', function () {
    $transaction = Transaction::factory()->withAmount(125000)->withActivePayment()->create();
    $payment = $transaction->payments()->firstOrFail();
    $notification = midtransNotificationFor($payment);
    unset($notification['order_id']);
    mockMidtransNotification($notification);

    $this->post('/webhook/midtrans/notification')->assertBadRequest();

    expect($payment->refresh()->status)->toBe(Payment::STATUS_ACTIVE);
    expect($transaction->refresh()->status)->toBe(Transaction::STATUS_PENDING);
});
