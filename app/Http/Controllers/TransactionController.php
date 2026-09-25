<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Exceptions\PaymentAlreadySettledException;
use App\Exceptions\PaymentException;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Throwable;

class TransactionController extends Controller
{
    public function __construct(private readonly TransactionService $transaction) {}

    public function index()
    {
        $user = Auth::user();

        return Inertia::render('Transaction/index', [
            'transactions' => $this->transaction->listForUser($user),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $transaction = $this->transaction->checkout(Auth::user());

            return redirect("/transactions/{$transaction->invoice_number}");
        } catch (CheckoutException $e) {
            return back()->with('form_error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            if (app()->hasDebugModeEnabled()) {
                throw $e;
            }

            return back()->with('form_error', 'Server failed to process your checkout. Please try again later.');
        }
    }

    public function cancel(Transaction $transaction)
    {
        try {
            abort_unless($transaction->user_id === Auth::id(), 404);

            if (! $transaction->isMutable()) {
                return back()->with('toast', [
                    'type' => 'error',
                    'message' => 'Only pending transactions can be cancelled.',
                ]);
            }

            $this->transaction->cancel($transaction);

            return redirect('/transactions')->with('toast', [
                'type' => 'success',
                'message' => 'Transaction cancelled successfully.',
            ]);
        } catch (Throwable $e) {
            report($e);

            if (app()->hasDebugModeEnabled()) {
                throw $e;
            }

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Failed to cancel the transaction. Please try again.',
            ]);
        }
    }

    public function init(Transaction $transaction)
    {
        try {
            abort_unless($transaction->user_id === Auth::id(), 404);

            $this->transaction->initPayment($transaction);

            return redirect("/transactions/{$transaction->invoice_number}")
                ->with('open_payment', true)
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Payment session ready.',
                ]);
        } catch (PaymentException $e) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable $e) {
            report($e);

            if (app()->hasDebugModeEnabled()) {
                throw $e;
            }

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Failed to start the payment session. Please try again.',
            ]);
        }
    }

    public function reset(Transaction $transaction)
    {
        try {
            abort_unless($transaction->user_id === Auth::id(), 404);

            $this->transaction->resetPayment($transaction);

            return redirect("/transactions/{$transaction->invoice_number}")
                ->with('open_payment', true)
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Payment session restarted.',
                ]);
        } catch (PaymentAlreadySettledException $e) {
            return redirect("/transactions/{$transaction->invoice_number}")->with('toast', [
                'type' => 'success',
                'message' => 'This payment has already been settled.',
            ]);
        } catch (PaymentException $e) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable $e) {
            report($e);

            if (app()->hasDebugModeEnabled()) {
                throw $e;
            }

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Failed to restart the payment session. Please try again.',
            ]);
        }
    }

    public function sync(Transaction $transaction)
    {
        try {
            abort_unless($transaction->user_id === Auth::id(), 404);

            $this->transaction->syncPayment($transaction);

            try {
                $this->transaction->closeExpired($transaction);
            } catch (Throwable $e) {
                report($e);
            }

            return redirect("/transactions/{$transaction->invoice_number}")->with('toast', [
                'type' => 'success',
                'message' => 'Payment status refreshed.',
            ]);
        } catch (Throwable $e) {
            report($e);

            if (app()->hasDebugModeEnabled()) {
                throw $e;
            }

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Failed to refresh the payment status. Please try again.',
            ]);
        }
    }

    public function show(Transaction $transaction)
    {
        abort_unless($transaction->user_id === Auth::id(), 404);

        $transaction->load(['items', 'user', 'activePayment', 'paidPayment']);

        $payment = null;
        $completedPayment = null;

        if ($transaction->isMutable()) {
            try {
                $payment = $this->transaction->paymentDetails($transaction);
            } catch (Throwable $e) {
                report($e);

                if (app()->hasDebugModeEnabled()) {
                    throw $e;
                }

                abort(500, 'Payment initialization failed.');
            }
        } elseif ($transaction->status === Transaction::STATUS_PAID) {
            $completedPayment = $this->transaction->completedPayment($transaction);
        }

        return Inertia::render('Transaction/show', [
            'transaction' => $transaction,
            'payment' => $payment,
            'completedPayment' => $completedPayment,
        ]);
    }
}
