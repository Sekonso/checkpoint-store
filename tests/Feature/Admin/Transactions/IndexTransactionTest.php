<?php

use App\Models\Transaction;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\UserFixtures;

it('lists the latest transactions with pagination', function () {
    $admin = UserFixtures::admin();
    $now = now();

    $this->travelTo($now->copy()->subDay());
    $older = Transaction::factory()->create();

    $this->travelTo($now);
    $newer = Transaction::factory()->create();

    $this->actingAs($admin)
        ->get('/admin/transactions')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Transactions/index')
            ->where('querySearch', null)
            ->where('queryStatus', null)
            ->has('paginatedTransactions.data', 2)
            ->where('paginatedTransactions.data.0.id', $newer->id)
            ->where('paginatedTransactions.data.1.id', $older->id)
        );
});

it('paginates transactions fifteen per page', function () {
    $admin = UserFixtures::admin();

    Transaction::factory()->count(16)->create();

    $this->actingAs($admin)
        ->get('/admin/transactions')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('paginatedTransactions.data', 15)
            ->where('paginatedTransactions.total', 16)
        );
});

it('filters transactions by case-insensitive invoice search', function () {
    $admin = UserFixtures::admin();
    $match = Transaction::factory()->create(['invoice_number' => 'INV-SEARCH-123']);
    Transaction::factory()->create(['invoice_number' => 'INV-OTHER-456']);

    $this->actingAs($admin)
        ->get('/admin/transactions?search=search')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('querySearch', 'search')
            ->has('paginatedTransactions.data', 1)
            ->where('paginatedTransactions.data.0.id', $match->id)
        );
});

it('filters transactions by status', function (string $status) {
    $admin = UserFixtures::admin();
    $match = Transaction::factory()->create(['status' => $status]);
    Transaction::factory()->create(['status' => $status === Transaction::STATUS_PENDING
        ? Transaction::STATUS_PAID
        : Transaction::STATUS_PENDING]);

    $this->actingAs($admin)
        ->get("/admin/transactions?status={$status}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('queryStatus', $status)
            ->has('paginatedTransactions.data', 1)
            ->where('paginatedTransactions.data.0.id', $match->id)
        );
})->with([
    'pending' => Transaction::STATUS_PENDING,
    'paid' => Transaction::STATUS_PAID,
    'cancelled' => Transaction::STATUS_CANCELLED,
]);

it('ignores an invalid status filter', function () {
    $admin = UserFixtures::admin();
    Transaction::factory()->pending()->create();
    Transaction::factory()->paid()->create();

    $this->actingAs($admin)
        ->get('/admin/transactions?status=unknown')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('queryStatus', 'unknown')
            ->has('paginatedTransactions.data', 2)
        );
});

it('combines invoice search and status filters', function () {
    $admin = UserFixtures::admin();
    $match = Transaction::factory()->pending()->create(['invoice_number' => 'INV-COMBINED-001']);
    Transaction::factory()->paid()->create(['invoice_number' => 'INV-COMBINED-002']);
    Transaction::factory()->pending()->create(['invoice_number' => 'INV-OTHER-003']);

    $this->actingAs($admin)
        ->get('/admin/transactions?search=combined&status=pending')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('paginatedTransactions.data', 1)
            ->where('paginatedTransactions.data.0.id', $match->id)
        );
});
