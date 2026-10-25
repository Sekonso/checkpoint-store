<?php

use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;

it('renders a mutable transaction with its items and no payment details when Midtrans is unconfigured', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for($user)->withItems()->withActivePayment()->create();

    config(['midtrans.server_key' => null, 'midtrans.client_key' => null]);

    $this->actingAs($user)
        ->get("/transactions/{$transaction->invoice_number}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Transaction/show')
            ->where('transaction.id', $transaction->id)
            ->has('transaction.items', 1)
            ->where('payment', null)
            ->where('completedPayment', null)
        );
});

it('returns not found when viewing another users transaction', function () {
    $transaction = Transaction::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get("/transactions/{$transaction->invoice_number}")
        ->assertNotFound();
});

it('renders a paid transaction with its completed payment', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for($user)->paid('qris')->withPaidPayment([
        'amount' => 125000,
        'paid_at' => now(),
    ])->create(['total_amount' => 125000]);

    $this->actingAs($user)
        ->get("/transactions/{$transaction->invoice_number}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Transaction/show')
            ->where('transaction.id', $transaction->id)
            ->where('payment', null)
            ->where('completedPayment.method', 'qris')
            ->where('completedPayment.amount', 125000)
            ->where('completedPayment.status', Payment::STATUS_PAID)
        );
});

it('does not include payment details for a cancelled transaction', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for($user)->cancelled()->create();

    $this->actingAs($user)
        ->get("/transactions/{$transaction->invoice_number}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Transaction/show')
            ->where('transaction.id', $transaction->id)
            ->where('payment', null)
            ->where('completedPayment', null)
        );
});
