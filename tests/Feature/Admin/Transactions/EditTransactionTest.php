<?php

use App\Models\Payment;
use App\Models\Transaction;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\UserFixtures;

it('renders the transaction with its customer and items', function () {
    $admin = UserFixtures::admin();
    $transaction = Transaction::factory()->withItems(2)->create();

    $this->actingAs($admin)
        ->get("/admin/transactions/{$transaction->id}/edit")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Transactions/edit')
            ->where('transaction.id', $transaction->id)
            ->where('transaction.user.id', $transaction->user_id)
            ->has('transaction.items', 2)
            ->where('completedPayment', null)
        );
});

it('renders completed payment details for a paid transaction', function () {
    $admin = UserFixtures::admin();
    $transaction = Transaction::factory()->paid('qris')->withPaidPayment([
        'amount' => 125000,
        'paid_at' => now(),
    ])->create(['total_amount' => 125000]);

    $this->actingAs($admin)
        ->get("/admin/transactions/{$transaction->id}/edit")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('transaction.id', $transaction->id)
            ->where('completedPayment.method', 'qris')
            ->where('completedPayment.amount', 125000)
            ->where('completedPayment.status', Payment::STATUS_PAID)
        );
});

it('does not include completed payment details for a cancelled transaction', function () {
    $admin = UserFixtures::admin();
    $transaction = Transaction::factory()->cancelled()->withCancelledPayment()->create();

    $this->actingAs($admin)
        ->get("/admin/transactions/{$transaction->id}/edit")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('transaction.id', $transaction->id)
            ->where('completedPayment', null)
        );
});

it('returns 404 for an unknown transaction', function () {
    $this->actingAs(UserFixtures::admin())
        ->get('/admin/transactions/999999/edit')
        ->assertNotFound();
});
