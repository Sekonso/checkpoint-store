<?php

namespace App\Services;

use App\Exceptions\PaymentAlreadySettledException;
use App\Exceptions\PaymentException;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Notification;
use Midtrans\Snap;
use Midtrans\Transaction as MidtransTransaction;
use Throwable;

final class PaymentService
{
    /**
     * Fixed lifetime of a transaction, in hours. Set once at checkout and
     * never extended: every payment session must fit inside this deadline.
     */
    public const TRANSACTION_TTL_HOURS = 24;

    public function isConfigured(): bool
    {
        return config('midtrans.server_key') && config('midtrans.client_key');
    }

    /**
     * Create (or reuse) the active payment session for a transaction.
     * Idempotent: concurrent calls resolve to a single active payment row.
     * Each session carries its own gateway order id (`{invoice}-{attempt}`),
     * so sessions never collide at Midtrans.
     */
    public function createPayment(Transaction $transaction): Payment
    {
        return DB::transaction(function () use ($transaction) {
            $locked = $this->lockMutable($transaction);

            $active = $locked->activePayment;

            if ($active) {
                return $active;
            }

            return $this->createSnapPayment($locked);
        });
    }

    /**
     * Retire the current payment session and start a new one with a fresh
     * snap token and a new gateway order id. When the previous session
     * already settled at Midtrans, the settlement is applied instead and no
     * new session is created.
     */
    public function resetPayment(Transaction $transaction): Payment
    {
        $locked = Transaction::lockForUpdate()->findOrFail($transaction->id);
        $this->assertMutable($locked);

        $old = $locked->activePayment ?? $locked->payments()->latest()->first();

        if (! $old) {
            return $this->createPayment($transaction);
        }

        $this->settleIfPaidAtGateway($old);

        $old->markCancelled();

        try {
            MidtransTransaction::cancel($old->order_id);
        } catch (Throwable $e) {
            report($e);
        }

        return DB::transaction(function () use ($locked) {
            $fresh = Transaction::lockForUpdate()->findOrFail($locked->id);
            $this->assertMutable($fresh);

            return $this->createSnapPayment($fresh);
        });
    }

    /**
     * Pull the current gateway state for a transaction's active session and
     * apply it locally. Used by the manual "Check status" action.
     */
    public function syncFromGateway(Transaction $transaction): Transaction
    {
        $active = $transaction->activePayment;

        if (! $active) {
            return $transaction->refresh();
        }

        return $this->syncPaymentRow($active);
    }

    /**
     * Pull the current gateway state for a single payment session and apply
     * it locally. Used by the reconcile command.
     */
    public function syncPaymentRow(Payment $payment): Transaction
    {
        try {
            $remote = self::normalizeRemote(MidtransTransaction::status($payment->order_id));
        } catch (Throwable $e) {
            report($e);

            return $payment->transaction->refresh();
        }

        $this->applyGatewayStatus($payment, (string) ($remote['transaction_status'] ?? ''), [
            'payment_type' => $remote['payment_type'] ?? null,
            'transaction_id' => $remote['transaction_id'] ?? null,
            'paid_at' => $remote['settlement_time'] ?? null,
        ]);

        return $payment->transaction->refresh();
    }

    /**
     * Apply a Midtrans status to a payment row and, on settlement, to the
     * transaction itself. Idempotent and race-safe. Only active rows change
     * state; a settlement arriving for a retired row still settles the
     * transaction (money was taken) and is logged for review.
     */
    public function applyGatewayStatus(Payment $payment, string $midtransStatus, array $metadata = []): void
    {
        $mappedStatus = Transaction::mapMidtransStatus($midtransStatus);

        if ($mappedStatus === null || $mappedStatus === 'pending') {
            return;
        }

        DB::transaction(function () use ($payment, $midtransStatus, $mappedStatus, $metadata) {
            $row = Payment::lockForUpdate()->findOrFail($payment->id);
            $locked = Transaction::lockForUpdate()->findOrFail($row->transaction_id);

            if ($mappedStatus === 'paid') {
                if ($locked->status === Transaction::STATUS_PAID) {
                    $applied = $row->markPaid(
                        $metadata['payment_type'] ?? null,
                        $metadata['transaction_id'] ?? null,
                        $metadata['paid_at'] ?? now(),
                    );

                    if (! $applied) {
                        Log::warning('Settlement received for a non-active payment session.', [
                            'order_id' => $row->order_id,
                            'midtrans_status' => $midtransStatus,
                        ]);
                    }

                    return;
                }

                if ($locked->status === Transaction::STATUS_CANCELLED && $locked->is_complete) {
                    $row->markPaid(
                        $metadata['payment_type'] ?? null,
                        $metadata['transaction_id'] ?? null,
                        $metadata['paid_at'] ?? now(),
                    );

                    $locked->loadMissing('items.product');
                    foreach ($locked->items as $item) {
                        if ($item->product) {
                            $item->product->decrement('stock', $item->quantity);
                        }
                    }

                    $locked->markPaid($metadata['payment_type'] ?? null, $metadata['paid_at'] ?? now());

                    return;
                }

                $applied = $row->markPaid(
                    $metadata['payment_type'] ?? null,
                    $metadata['transaction_id'] ?? null,
                    $metadata['paid_at'] ?? now(),
                );

                if (! $applied) {
                    Log::warning('Settlement received for a non-active payment session.', [
                        'order_id' => $row->order_id,
                        'midtrans_status' => $midtransStatus,
                    ]);
                }

                $locked->markPaid($metadata['payment_type'] ?? null, $metadata['paid_at'] ?? now());

                return;
            }

            if ($locked->is_complete && $locked->status === Transaction::STATUS_PAID) {
                return;
            }

            if ($row->isActive()) {
                if (! blank($metadata['transaction_id'] ?? null)) {
                    $row->update(['midtrans_transaction_id' => $metadata['transaction_id']]);
                }

                if ($midtransStatus === 'expire') {
                    $row->markExpired();
                } else {
                    $row->markCancelled();
                }
            }
        });
    }

    public function cancelGatewaySession(Payment $payment): void
    {
        MidtransTransaction::cancel($payment->order_id);
    }

    public function expireGatewaySession(Payment $payment): void
    {
        MidtransTransaction::expire($payment->order_id);
    }

    public function readNotification(): array
    {
        $notif = new Notification;

        return [
            'order_id' => $notif->order_id,
            'status' => $notif->transaction_status,
            'payment_type' => $notif->payment_type,
            'transaction_id' => $notif->transaction_id,
            'settlement_time' => $notif->settlement_time,
            'status_code' => $notif->status_code ?? null,
            'gross_amount' => $notif->gross_amount ?? null,
            'signature_key' => $notif->signature_key ?? null,
        ];
    }

    public function verifySignature(array $data): bool
    {
        if (blank($data['order_id'] ?? null) || blank($data['signature_key'] ?? null)) {
            return false;
        }

        $expected = hash('sha512', implode('', [
            $data['order_id'],
            $data['status_code'] ?? '',
            $data['gross_amount'] ?? '',
            config('midtrans.server_key'),
        ]));

        return hash_equals($expected, (string) $data['signature_key']);
    }

    // Internals

    private function lockMutable(Transaction $transaction): Transaction
    {
        $locked = Transaction::lockForUpdate()->findOrFail($transaction->id);
        $this->assertMutable($locked);

        return $locked;
    }

    private function assertMutable(Transaction $transaction): void
    {
        if (! $transaction->isMutable()) {
            throw new PaymentException('This transaction can no longer accept payment actions.');
        }

        if ($transaction->expires_at !== null && $transaction->expires_at->isPast()) {
            throw new PaymentException('This transaction has expired. Please create a new order.');
        }
    }

    /**
     * Create a new snap session row with its own gateway order id. On a
     * concurrent-insert race the partial unique index rejects the second row;
     * fall back to the winning row.
     */
    private function createSnapPayment(Transaction $transaction): Payment
    {
        $attempt = $transaction->payments()->count() + 1;
        $orderId = "{$transaction->invoice_number}-{$attempt}";
        $expiresAt = $transaction->expires_at ?? now()->addHours(self::TRANSACTION_TTL_HOURS);

        $snap = Snap::createTransaction($this->buildPayload($transaction, $orderId, $expiresAt));

        try {
            $payment = $transaction->payments()->create([
                'order_id' => $orderId,
                'snap_token' => $snap->token,
                'status' => Payment::STATUS_ACTIVE,
                'amount' => $transaction->total_amount,
                'expires_at' => $expiresAt,
            ]);
        } catch (QueryException $e) {
            if (! in_array($e->getCode(), ['23000', '23505'], true)) {
                throw $e;
            }

            $payment = $transaction->activePayment()->firstOrFail();
        }

        return $payment->refresh();
    }

    /**
     * When the previous session already settled at the gateway, apply the
     * settlement locally instead of resetting.
     *
     * @throws PaymentAlreadySettledException
     */
    private function settleIfPaidAtGateway(Payment $payment): void
    {
        try {
            $remote = self::normalizeRemote(MidtransTransaction::status($payment->order_id));
        } catch (Throwable $e) {
            report($e);

            return;
        }

        $remoteStatus = (string) ($remote['transaction_status'] ?? '');

        if (! in_array($remoteStatus, ['settlement', 'capture'], true)) {
            return;
        }

        $this->applyGatewayStatus($payment, $remoteStatus, [
            'payment_type' => $remote['payment_type'] ?? null,
            'transaction_id' => $remote['transaction_id'] ?? null,
            'paid_at' => $remote['settlement_time'] ?? null,
        ]);

        throw new PaymentAlreadySettledException('This payment has already been settled.');
    }

    /**
     * Normalize a Midtrans API response (object in production, JSON string
     * under the test stub) into an array.
     */
    private static function normalizeRemote(mixed $remote): array
    {
        if (is_string($remote)) {
            return (array) json_decode($remote, true);
        }

        return (array) $remote;
    }

    private function buildPayload(Transaction $transaction, string $orderId, mixed $expiresAt): array
    {
        $transaction->loadMissing(['user', 'items']);

        $customerDetails = [
            'first_name' => $transaction->name,
            'email' => $transaction->user->email,
            'phone' => $transaction->phone,
            'billing_address' => [
                'address' => $transaction->address,
            ],
        ];

        return [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $transaction->total_amount,
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'minute',
                'duration' => max(1, (int) now()->diffInMinutes($expiresAt)),
            ],
            'item_details' => $transaction->items
                ->map(fn ($item) => [
                    'id' => $item->product_id,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'name' => Str::limit($item->product_name, 50),
                ])
                ->values()
                ->all(),
            'customer_details' => $customerDetails,
        ];
    }
}
