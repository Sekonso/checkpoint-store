<?php

use App\Models\Transaction;
use App\Models\User;

it('renders the authenticated user transactions', function () {
    $user = User::factory()->create();
    $ownTransaction = Transaction::factory()->for($user)->create();
    $ownPaidTransaction = Transaction::factory()->for($user)->paid()->create();
    $otherTransaction = Transaction::factory()->create();
    $cancelledTransaction = Transaction::factory()->for($user)->cancelled()->create();

    $this->actingAs($user)
        ->get('/transactions')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Transaction/index')
            ->has('transactions.pending', 1)
            ->where('transactions.pending.0.id', $ownTransaction->id)
            ->has('transactions.completed', 1)
            ->where('transactions.completed.0.id', $ownPaidTransaction->id)
        );

    expect($otherTransaction->user_id)->not->toBe($user->id)
        ->and($cancelledTransaction->status)->toBe(Transaction::STATUS_CANCELLED);
});
