<?php

use App\Models\Transaction;
use Tests\Support\UserFixtures;

it('updates a paid transaction shipping status', function () {
    $admin = UserFixtures::admin();
    $transaction = Transaction::factory()->paid()->create();

    $this->actingAs($admin)
        ->from("/admin/transactions/{$transaction->id}/edit")
        ->patch("/admin/transactions/{$transaction->id}/shipping", [
            'shipping_status' => Transaction::SHIPPING_IN_TRANSIT,
        ])
        ->assertRedirect("/admin/transactions/{$transaction->id}/edit")
        ->assertSessionHas('toast', [
            'type' => 'success',
            'message' => 'Shipping status updated.',
        ]);

    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'shipping_status' => Transaction::SHIPPING_IN_TRANSIT,
    ]);
});

it('rejects a missing shipping status', function () {
    $admin = UserFixtures::admin();
    $transaction = Transaction::factory()->paid()->create();

    $this->actingAs($admin)
        ->from("/admin/transactions/{$transaction->id}/edit")
        ->patch("/admin/transactions/{$transaction->id}/shipping", [])
        ->assertSessionHasErrors('shipping_status');

    expect($transaction->refresh()->shipping_status)->toBe(Transaction::SHIPPING_IDLE);
});

it('rejects an unsupported shipping status', function () {
    $admin = UserFixtures::admin();
    $transaction = Transaction::factory()->paid()->create();

    $this->actingAs($admin)
        ->from("/admin/transactions/{$transaction->id}/edit")
        ->patch("/admin/transactions/{$transaction->id}/shipping", [
            'shipping_status' => 'returned',
        ])
        ->assertSessionHasErrors('shipping_status');

    expect($transaction->refresh()->shipping_status)->toBe(Transaction::SHIPPING_IDLE);
});

it('rejects a shipping update for a transaction that is not paid', function (string $status) {
    $admin = UserFixtures::admin();
    $transaction = Transaction::factory()->create(['status' => $status]);

    $this->actingAs($admin)
        ->from("/admin/transactions/{$transaction->id}/edit")
        ->patch("/admin/transactions/{$transaction->id}/shipping", [
            'shipping_status' => Transaction::SHIPPING_PACKAGING,
        ])
        ->assertSessionHasErrors('shipping_status');

    expect($transaction->refresh()->shipping_status)->toBe(Transaction::SHIPPING_IDLE);
})->with([
    'pending' => Transaction::STATUS_PENDING,
    'cancelled' => Transaction::STATUS_CANCELLED,
]);

it('returns 404 when updating an unknown transaction', function () {
    $this->actingAs(UserFixtures::admin())
        ->patch('/admin/transactions/999999/shipping', [
            'shipping_status' => Transaction::SHIPPING_PACKAGING,
        ])
        ->assertNotFound();
});
