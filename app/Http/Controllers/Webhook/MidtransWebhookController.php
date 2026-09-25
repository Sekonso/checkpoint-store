<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;
use Throwable;

class MidtransWebhookController extends Controller
{
    public function __construct(private readonly PaymentService $payment)
    {
    }

    public function notification()
    {
        try {
            $data = $this->payment->readNotification();

            $orderId = $data['order_id'];

            if (!is_string($orderId) || blank($orderId)) {
                return response('No order id provided.', 400);
            }

            if (!$this->payment->verifySignature($data)) {
                Log::warning('Rejected Midtrans notification with invalid signature.', [
                    'order_id' => $orderId,
                ]);

                return response('Invalid signature.', 403);
            }

            $payment = Payment::where('order_id', $orderId)->first();

            if (!$payment || !$payment->transaction) {
                return response('Payment not found.', 404);
            }

            if ((int) ($data['gross_amount'] ?? 0) !== (int) $payment->amount) {
                Log::warning('Rejected Midtrans notification with mismatched amount.', [
                    'order_id' => $orderId,
                ]);

                return response('Amount mismatch.', 422);
            }

            $this->payment->applyGatewayStatus($payment, $data['status'], [
                'payment_type' => $data['payment_type'],
                'transaction_id' => $data['transaction_id'],
                'paid_at' => $data['settlement_time'] ?: null,
            ]);

            return response('OK', 200);
        } catch (Throwable $e) {
            report($e);

            return response('Failed to process notification.', 500);
        }
    }
}
