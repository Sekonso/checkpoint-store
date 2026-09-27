<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTransactionShippingRequest;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function __construct(private readonly TransactionService $transaction)
    {
    }

    public function index(Request $request)
    {
        $query_search = $request->query('search');
        $query_status = $request->query('status');

        return Inertia::render('Admin/Transactions/index', [
            'querySearch' => $query_search,
            'queryStatus' => $query_status,
            'paginatedTransactions' => $this->transaction->paginateForAdmin($query_search, $query_status),
        ]);
    }

    public function edit(Transaction $transaction)
    {
        $transaction->load(['user', 'items']);

        $completed_payment = $transaction->status === Transaction::STATUS_PAID
            ? $this->transaction->completedPayment($transaction)
            : null;

        return Inertia::render('Admin/Transactions/edit', [
            'transaction' => $transaction,
            'completedPayment' => $completed_payment,
        ]);
    }

    public function updateShipping(UpdateTransactionShippingRequest $request, Transaction $transaction)
    {
        try {
            $this->transaction->updateShipping($transaction, $request->validated('shipping_status'));

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'Shipping status updated.',
            ]);
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->with('toast', [
                    'type' => 'error',
                    'message' => $e->validator->errors()->first(),
                ]);
        } catch (\Throwable $e) {
            report($e);

            if (app()->hasDebugModeEnabled()) {
                throw $e;
            }

            return back()
                ->withErrors(['shipping_status' => 'Failed to update the shipping status.'])
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Failed to update the shipping status.',
                ]);
        }
    }

    public function destroy(Transaction $transaction)
    {
        try {
            $this->transaction->deleteForAdmin($transaction);

            return back();
        } catch (\Throwable $e) {
            report($e);

            if (app()->hasDebugModeEnabled()) {
                throw $e;
            }

            throw ValidationException::withMessages([
                'delete' => 'Failed to delete transaction.',
            ]);
        }
    }
}
