<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

final class TransactionService
{
    public function __construct(private readonly PaymentService $payment) {}

    public function listForUser(User $user): Collection
    {
        return Transaction::with('items')
            ->where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(function ($transaction) {
                return $transaction->status === 'pending' ? 'pending' : 'completed';
            });
    }

    public function checkout(User $user): Transaction
    {
        if (blank($user->phone) || blank($user->address)) {
            throw new CheckoutException('Please fill out phone and address on the profile before checking out.');
        }

        $cart = $user->cart()->with('items.product')->first();

        if (! $cart || $cart->items->isEmpty()) {
            throw new CheckoutException('Your cart is empty.');
        }

        foreach ($cart->items as $item) {
            if ($item->quantity > $item->product->stock) {
                throw new CheckoutException("Stock for {$item->product->name} is not available.");
            }
        }

        $transaction = DB::transaction(function () use ($user, $cart) {
            $totalAmount = $cart->items->reduce(function ($total, $item) {
                return $total + ($item->product->price * $item->quantity);
            }, 0);

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'address' => $user->address,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'expires_at' => now()->addHours(PaymentService::TRANSACTION_TTL_HOURS),
            ]);

            foreach ($cart->items as $item) {
                $subtotal = $item->product->price * $item->quantity;

                $transaction->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $subtotal,
                ]);

                $item->product->decrement('stock', $item->quantity);
            }

            $cart->items()->delete();

            return $transaction;
        });

        return $transaction;
    }

    public function cancel(Transaction $transaction): void
    {
        $active = $transaction->activePayment;

        if ($active) {
            try {
                $this->payment->cancelGatewaySession($active);
            } catch (Throwable $e) {
                report($e);
            }
        }

        DB::transaction(function () use ($transaction) {
            $locked = Transaction::lockForUpdate()->findOrFail($transaction->id);

            $locked->activePayment?->markCancelled();

            $locked->load('items.product');

            foreach ($locked->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            $locked->markCancelled();
        });
    }

    /**
     * Close an abandoned transaction (deadline passed, no active payment).
     * No gateway call: there is no live session left to cancel.
     */
    public function closeExpired(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $locked = Transaction::lockForUpdate()->findOrFail($transaction->id);

            if (! $locked->isMutable()) {
                return;
            }

            if ($locked->expires_at === null || $locked->expires_at->isFuture()) {
                return;
            }

            if ($locked->status === Transaction::STATUS_PAID) {
                return;
            }

            $locked->load('items.product');

            foreach ($locked->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            $locked->markCancelled();
        });
    }

    public function initPayment(Transaction $transaction): Payment
    {
        return $this->payment->createPayment($transaction);
    }

    public function resetPayment(Transaction $transaction): Payment
    {
        return $this->payment->resetPayment($transaction);
    }

    public function syncPayment(Transaction $transaction): Transaction
    {
        return $this->payment->syncFromGateway($transaction);
    }

    public function paymentDetails(Transaction $transaction): ?array
    {
        if (! $this->payment->isConfigured()) {
            return null;
        }

        $active = $transaction->activePayment;

        if (! $active) {
            return null;
        }

        return [
            'snap_token' => $active->snap_token,
            'client_key' => config('midtrans.client_key'),
            'is_production' => (bool) config('midtrans.is_production'),
        ];
    }

    public function completedPayment(Transaction $transaction): ?array
    {
        $paid = $transaction->paidPayment;

        if (! $paid) {
            return null;
        }

        return [
            'method' => $paid->method ?? $transaction->payment_type,
            'amount' => $paid->amount,
            'paid_at' => $paid->paid_at,
            'status' => $paid->status,
        ];
    }
}
