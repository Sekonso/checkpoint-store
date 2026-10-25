<?php

use App\Models\Transaction;
use App\Models\User;

it('cancels a mutable transaction owned by the user', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for($user)->withItems()->create();
    $item = $transaction->items()->first();
    $product = $item->product;
    $product->update(['stock' => 3]);
    $item->update(['quantity' => 2]);

    $this->actingAs($user)
        ->post("/transactions/{$transaction->invoice_number}/cancel")
        ->assertRedirect('/transactions')
        ->assertSessionHas('toast.type', 'success');

    expect($transaction->refresh()->status)->toBe(Transaction::STATUS_CANCELLED)
        ->and($product->refresh()->stock)->toBe(5);
});

it('returns not found when cancelling another users transaction', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->create();

    $this->actingAs($user)
        ->post("/transactions/{$transaction->invoice_number}/cancel")
        ->assertNotFound();

    expect($transaction->refresh()->status)->toBe(Transaction::STATUS_PENDING);
});

it('rejects cancellation when the transaction is not mutable', function (string $status) {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for($user)->create([
        'status' => $status,
        'is_complete' => true,
    ]);

    $this->actingAs($user)
        ->from("/transactions/{$transaction->invoice_number}")
        ->post("/transactions/{$transaction->invoice_number}/cancel")
        ->assertRedirect("/transactions/{$transaction->invoice_number}")
        ->assertSessionHas('toast.type', 'error');

    expect($transaction->refresh()->status)->toBe($status);
})->with([
    'paid' => Transaction::STATUS_PAID,
    'cancelled' => Transaction::STATUS_CANCELLED,
]);
