<?php

use App\Models\Transaction;
use App\Models\User;

it('reuses the active payment session for the users transaction', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for($user)->withActivePayment()->create();

    $this->actingAs($user)
        ->post("/transactions/{$transaction->invoice_number}/payments")
        ->assertRedirect("/transactions/{$transaction->invoice_number}")
        ->assertSessionHas('open_payment', true)
        ->assertSessionHas('toast.type', 'success');

    expect($transaction->payments()->count())->toBe(1);
});

it('returns not found when starting payment for another users transaction', function () {
    $transaction = Transaction::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post("/transactions/{$transaction->invoice_number}/payments")
        ->assertNotFound();
});

it('returns a payment error when the transaction is already paid', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for($user)->paid()->create();

    $this->actingAs($user)
        ->from('/transactions')
        ->post("/transactions/{$transaction->invoice_number}/payments")
        ->assertRedirect('/transactions')
        ->assertSessionHas('toast.message', 'This transaction can no longer accept payment actions.');
});
