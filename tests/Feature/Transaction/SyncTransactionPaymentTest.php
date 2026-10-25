<?php

use App\Models\Transaction;
use App\Models\User;

it('refreshes payment status when the transaction has no active payment session', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for($user)->create();

    $this->actingAs($user)
        ->post("/transactions/{$transaction->invoice_number}/payments/sync")
        ->assertRedirect("/transactions/{$transaction->invoice_number}")
        ->assertSessionHas('toast.message', 'Payment status refreshed.');

    expect($transaction->refresh()->status)->toBe(Transaction::STATUS_PENDING);
});

it('returns not found when synchronizing another users transaction', function () {
    $transaction = Transaction::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post("/transactions/{$transaction->invoice_number}/payments/sync")
        ->assertNotFound();
});

it('closes an expired mutable transaction after refreshing payment status', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for($user)->create([
        'expires_at' => now()->subMinute(),
    ]);

    $this->actingAs($user)
        ->post("/transactions/{$transaction->invoice_number}/payments/sync")
        ->assertRedirect("/transactions/{$transaction->invoice_number}")
        ->assertSessionHas('toast.message', 'Payment status refreshed.');

    expect($transaction->refresh()->status)->toBe(Transaction::STATUS_CANCELLED);
});
