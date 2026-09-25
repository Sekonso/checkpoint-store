<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\Transaction;
use App\Services\PaymentService;
use App\Services\TransactionService;
use Illuminate\Console\Command;
use Throwable;

class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile';

    protected $description = 'Sync stale payment sessions with the gateway and close abandoned transactions.';

    public function handle(PaymentService $payment, TransactionService $transactions): int
    {
        $synced = 0;
        $failedSync = 0;

        Payment::with('transaction')
            ->where('status', Payment::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->where('expires_at', '<', now())
                    ->orWhere('created_at', '<', now()->subDay());
            })
            ->chunkById(100, function ($payments) use ($payment, &$synced, &$failedSync) {
                foreach ($payments as $row) {
                    if (! $row->transaction) {
                        continue;
                    }

                    try {
                        $payment->syncPaymentRow($row);
                        $synced++;
                    } catch (Throwable $e) {
                        report($e);
                        $failedSync++;
                    }
                }
            });

        $closed = 0;

        Transaction::where('status', Transaction::STATUS_PENDING)
            ->where('is_complete', false)
            ->where('expires_at', '<', now())
            ->chunkById(100, function ($abandoned) use ($transactions, &$closed) {
                foreach ($abandoned as $transaction) {
                    try {
                        $transactions->closeExpired($transaction);
                        $closed++;
                    } catch (Throwable $e) {
                        report($e);
                    }
                }
            });

        $this->info("Synced {$synced} payment(s), failed {$failedSync} payment sync(s), closed {$closed} abandoned transaction(s).");

        return self::SUCCESS;
    }
}
