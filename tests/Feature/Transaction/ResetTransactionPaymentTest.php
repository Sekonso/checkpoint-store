<?php

use App\Models\Transaction;
use App\Models\User;

it('returns not found when restarting payment for another users transaction', function () {
    $transaction = Transaction::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post("/transactions/{$transaction->invoice_number}/payments/reset")
        ->assertNotFound();
});

it('returns a payment error when the transaction is already paid', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for($user)->paid()->create();

    $this->actingAs($user)
        ->from('/transactions')
        ->post("/transactions/{$transaction->invoice_number}/payments/reset")
        ->assertRedirect('/transactions')
        ->assertSessionHas('toast.message', 'This transaction can no longer accept payment actions.');
});
