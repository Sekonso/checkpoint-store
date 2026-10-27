<?php

use App\Models\Transaction;
use Tests\Support\UserFixtures;

it('deletes a cancelled transaction', function () {
    $admin = UserFixtures::admin();
    $transaction = Transaction::factory()->cancelled()->create();

    $this->actingAs($admin)
        ->delete("/admin/transactions/{$transaction->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
});

it('rejects deletion of a transaction that is not cancelled', function (string $status) {
    $admin = UserFixtures::admin();
    $transaction = Transaction::factory()->create(['status' => $status]);

    $this->actingAs($admin)
        ->from('/admin/transactions')
        ->delete("/admin/transactions/{$transaction->id}")
        ->assertSessionHasErrors('delete');

    $this->assertModelExists($transaction);
})->with([
    'pending' => Transaction::STATUS_PENDING,
    'paid' => Transaction::STATUS_PAID,
]);

it('returns 404 when deleting an unknown transaction', function () {
    $this->actingAs(UserFixtures::admin())
        ->delete('/admin/transactions/999999')
        ->assertNotFound();
});
