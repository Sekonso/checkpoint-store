<?php

require_once __DIR__.'/../Support/midtrans_stub.php';

use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Midtrans\Config;
use Midtrans\MT_Tests;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::$serverKey = 'SB-Mid-server-test';
    Config::$clientKey = 'SB-Mid-client-test';
    MT_Tests::$stubHttp = true;
});

afterEach(function () {
    MT_Tests::$stubHttp = false;
    MT_Tests::$stubHttpResponse = null;
    MT_Tests::$lastHttpRequest = [];
});

function seedAdminTransaction(
    string $status = Transaction::STATUS_PENDING,
    ?string $invoiceNumber = null,
    ?User $user = null,
): Transaction {
    $user ??= User::factory()->create([
        'name' => 'Budi',
        'phone' => '08123',
        'address' => 'Jl. Test 1',
    ]);

    $transaction = Transaction::create([
        'user_id' => $user->id,
        'invoice_number' => $invoiceNumber,
        'name' => $user->name,
        'phone' => $user->phone,
        'address' => $user->address,
        'total_amount' => 200000,
        'status' => $status,
        'expires_at' => now()->addDay(),
        'is_complete' => in_array($status, [Transaction::STATUS_PAID, Transaction::STATUS_CANCELLED], true),
    ]);

    $transaction->items()->create([
        'product_name' => 'Headset Pro',
        'price' => 100000,
        'quantity' => 2,
        'subtotal' => 200000,
    ]);

    return $transaction;
}

it('blocks non admins from the transaction list', function () {
    $customer = User::factory()->create();
    $transaction = seedAdminTransaction();

    $this->actingAs($customer)->get('/admin/transactions')->assertForbidden();

    $this->actingAs($customer)
        ->get("/admin/transactions/{$transaction->id}/edit")
        ->assertForbidden();
});

it('lists the latest transaction first', function () {
    $admin = User::factory()->asAdmin()->create();

    $older = seedAdminTransaction();
    $older->forceFill(['created_at' => now()->subWeek()])->save();

    $newer = seedAdminTransaction();

    $this->actingAs($admin)
        ->get('/admin/transactions')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Transactions/index')
            ->where('querySearch', null)
            ->where('queryStatus', null)
            ->has('paginatedTransactions.data', 2)
            ->where('paginatedTransactions.data.0.id', $newer->id)
            ->where('paginatedTransactions.data.1.id', $older->id)
        );
});

it('searches transactions by invoice number', function () {
    $admin = User::factory()->asAdmin()->create();

    $match = seedAdminTransaction(Transaction::STATUS_PENDING, 'INV-20260927-ALPHA01');
    $other = seedAdminTransaction(Transaction::STATUS_PENDING, 'INV-20260927-BRAVO02');

    $this->actingAs($admin)
        ->get('/admin/transactions?search=alpha')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('querySearch', 'alpha')
            ->has('paginatedTransactions.data', 1)
            ->where('paginatedTransactions.data.0.id', $match->id)
            ->where('paginatedTransactions.total', 1)
            ->etc()
        );

    expect($other->exists)->toBeTrue();
});

it('filters transactions by status', function () {
    $admin = User::factory()->asAdmin()->create();

    $paid = seedAdminTransaction(Transaction::STATUS_PAID);
    $paid->update(['paid_at' => now()]);

    $pending = seedAdminTransaction(Transaction::STATUS_PENDING);
    $cancelled = seedAdminTransaction(Transaction::STATUS_CANCELLED);

    $this->actingAs($admin)
        ->get('/admin/transactions?status=pending')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('queryStatus', 'pending')
            ->has('paginatedTransactions.data', 1)
            ->where('paginatedTransactions.data.0.id', $pending->id)
            ->etc()
        );

    $this->actingAs($admin)
        ->get('/admin/transactions?status=paid')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('paginatedTransactions.data', 1)
            ->where('paginatedTransactions.data.0.id', $paid->id)
            ->etc()
        );

    $this->actingAs($admin)
        ->get('/admin/transactions?status=cancelled')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('paginatedTransactions.data', 1)
            ->where('paginatedTransactions.data.0.id', $cancelled->id)
            ->etc()
        );
});

it('ignores an unknown status filter', function () {
    $admin = User::factory()->asAdmin()->create();

    seedAdminTransaction();
    seedAdminTransaction();

    $this->actingAs($admin)
        ->get('/admin/transactions?status=not-a-status')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('queryStatus', 'not-a-status')
            ->has('paginatedTransactions.data', 2)
            ->etc()
        );
});

it('shows customer, items and payment data on the transaction edit page', function () {
    $admin = User::factory()->asAdmin()->create();
    $customer = User::factory()->create([
        'name' => 'Budi',
        'phone' => '08123',
        'address' => 'Jl. Test 1',
    ]);

    $transaction = seedAdminTransaction(Transaction::STATUS_PAID, null, $customer);
    $transaction->update(['paid_at' => now()]);

    $transaction->payments()->create([
        'order_id' => "{$transaction->invoice_number}-1",
        'snap_token' => 'stored-token',
        'status' => Payment::STATUS_PAID,
        'method' => 'gopay',
        'amount' => 200000,
        'paid_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get("/admin/transactions/{$transaction->id}/edit")
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Transactions/edit')
            ->where('transaction.id', $transaction->id)
            ->where('transaction.user.email', $customer->email)
            ->has('transaction.items', 1)
            ->where('transaction.items.0.product_name', 'Headset Pro')
            ->where('completedPayment.method', 'gopay')
            ->where('completedPayment.amount', 200000)
        );
});

it('omits payment data for a transaction that is not paid', function () {
    $admin = User::factory()->asAdmin()->create();
    $transaction = seedAdminTransaction();

    $this->actingAs($admin)
        ->get("/admin/transactions/{$transaction->id}/edit")
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('completedPayment', null)
            ->etc()
        );
});

it('refuses to delete a transaction that is not cancelled', function (string $status) {
    $admin = User::factory()->asAdmin()->create();
    $transaction = seedAdminTransaction($status);

    if ($status === Transaction::STATUS_PAID) {
        $transaction->update(['paid_at' => now()]);
    }

    $this->actingAs($admin)
        ->delete("/admin/transactions/{$transaction->id}")
        ->assertSessionHasErrors('delete');

    expect(Transaction::find($transaction->id))->not->toBeNull();
})->with([
    Transaction::STATUS_PENDING,
    Transaction::STATUS_PAID,
]);

it('deletes a cancelled transaction together with its items and payments', function () {
    $admin = User::factory()->asAdmin()->create();
    $transaction = seedAdminTransaction(Transaction::STATUS_CANCELLED);

    $transaction->payments()->create([
        'order_id' => "{$transaction->invoice_number}-1",
        'snap_token' => 'retired-token',
        'status' => Payment::STATUS_CANCELLED,
        'amount' => 200000,
    ]);

    $this->actingAs($admin)
        ->from('/admin/transactions')
        ->delete("/admin/transactions/{$transaction->id}")
        ->assertRedirect('/admin/transactions')
        ->assertSessionHasNoErrors();

    expect(Transaction::find($transaction->id))->toBeNull()
        ->and(Payment::where('transaction_id', $transaction->id)->count())->toBe(0)
        ->and($transaction->items()->count())->toBe(0);
});

it('cancels a leftover payment session at the gateway before deleting', function () {
    $admin = User::factory()->asAdmin()->create();
    $transaction = seedAdminTransaction(Transaction::STATUS_CANCELLED);

    $transaction->payments()->create([
        'order_id' => "{$transaction->invoice_number}-1",
        'snap_token' => 'live-token',
        'status' => Payment::STATUS_ACTIVE,
        'amount' => 200000,
    ]);

    $this->actingAs($admin)
        ->from('/admin/transactions')
        ->delete("/admin/transactions/{$transaction->id}")
        ->assertRedirect('/admin/transactions');

    $url = MT_Tests::$lastHttpRequest['url'] ?? '';

    expect($url)->toContain("/v2/{$transaction->invoice_number}-1/cancel")
        ->and(Transaction::find($transaction->id))->toBeNull();
});

it('blocks non admins from changing the shipping status', function () {
    $customer = User::factory()->create();
    $transaction = seedAdminTransaction(Transaction::STATUS_PAID);

    $this->actingAs($customer)
        ->patch("/admin/transactions/{$transaction->id}/shipping", [
            'shipping_status' => Transaction::SHIPPING_IN_TRANSIT,
        ])
        ->assertForbidden();

    expect($transaction->refresh()->shipping_status)->toBe(Transaction::SHIPPING_IDLE);
});

it('defaults the shipping status to idle', function () {
    $transaction = seedAdminTransaction(Transaction::STATUS_PAID);

    expect($transaction->refresh()->shipping_status)->toBe(Transaction::SHIPPING_IDLE);
});

it('changes the shipping status of a paid transaction', function (string $shippingStatus) {
    $admin = User::factory()->asAdmin()->create();
    $transaction = seedAdminTransaction(Transaction::STATUS_PAID);
    $transaction->update(['paid_at' => now()]);

    $this->actingAs($admin)
        ->from("/admin/transactions/{$transaction->id}/edit")
        ->patch("/admin/transactions/{$transaction->id}/shipping", [
            'shipping_status' => $shippingStatus,
        ])
        ->assertRedirect("/admin/transactions/{$transaction->id}/edit")
        ->assertSessionHasNoErrors();

    expect($transaction->refresh()->shipping_status)->toBe($shippingStatus);
})->with([
    Transaction::SHIPPING_IDLE,
    Transaction::SHIPPING_PACKAGING,
    Transaction::SHIPPING_IN_TRANSIT,
    Transaction::SHIPPING_DELIVERED,
]);

it('can move the shipping status backwards', function () {
    $admin = User::factory()->asAdmin()->create();
    $transaction = seedAdminTransaction(Transaction::STATUS_PAID);
    $transaction->update([
        'paid_at' => now(),
        'shipping_status' => Transaction::SHIPPING_DELIVERED,
    ]);

    $this->actingAs($admin)
        ->patch("/admin/transactions/{$transaction->id}/shipping", [
            'shipping_status' => Transaction::SHIPPING_IN_TRANSIT,
        ])
        ->assertSessionHasNoErrors();

    expect($transaction->refresh()->shipping_status)->toBe(Transaction::SHIPPING_IN_TRANSIT);
});

it('rejects an unknown shipping status', function () {
    $admin = User::factory()->asAdmin()->create();
    $transaction = seedAdminTransaction(Transaction::STATUS_PAID);

    $this->actingAs($admin)
        ->patch("/admin/transactions/{$transaction->id}/shipping", [
            'shipping_status' => 'teleported',
        ])
        ->assertSessionHasErrors('shipping_status');

    expect($transaction->refresh()->shipping_status)->toBe(Transaction::SHIPPING_IDLE);
});

it('requires a shipping status', function () {
    $admin = User::factory()->asAdmin()->create();
    $transaction = seedAdminTransaction(Transaction::STATUS_PAID);

    $this->actingAs($admin)
        ->patch("/admin/transactions/{$transaction->id}/shipping", [
            'shipping_status' => null,
        ])
        ->assertSessionHasErrors('shipping_status');

    expect($transaction->refresh()->shipping_status)->toBe(Transaction::SHIPPING_IDLE);
});

it('refuses to change the shipping status of a transaction that is not paid', function (string $status) {
    $admin = User::factory()->asAdmin()->create();
    $transaction = seedAdminTransaction($status);

    $this->actingAs($admin)
        ->patch("/admin/transactions/{$transaction->id}/shipping", [
            'shipping_status' => Transaction::SHIPPING_DELIVERED,
        ])
        ->assertSessionHasErrors('shipping_status');

    expect($transaction->refresh()->shipping_status)->toBe(Transaction::SHIPPING_IDLE);
})->with([
    Transaction::STATUS_PENDING,
    Transaction::STATUS_CANCELLED,
]);

it('exposes the shipping status on the admin edit page', function () {
    $admin = User::factory()->asAdmin()->create();
    $transaction = seedAdminTransaction(Transaction::STATUS_PAID);
    $transaction->update([
        'paid_at' => now(),
        'shipping_status' => Transaction::SHIPPING_PACKAGING,
    ]);

    $this->actingAs($admin)
        ->get("/admin/transactions/{$transaction->id}/edit")
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Transactions/edit')
            ->where('transaction.shipping_status', Transaction::SHIPPING_PACKAGING)
        );
});

it('exposes the shipping status to the owner of a paid transaction', function () {
    $customer = User::factory()->create([
        'name' => 'Budi',
        'phone' => '08123',
        'address' => 'Jl. Test 1',
    ]);
    $transaction = seedAdminTransaction(Transaction::STATUS_PAID, null, $customer);
    $transaction->update([
        'paid_at' => now(),
        'shipping_status' => Transaction::SHIPPING_IN_TRANSIT,
    ]);

    $this->actingAs($customer)
        ->get("/transactions/{$transaction->invoice_number}")
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Transaction/show')
            ->where('transaction.shipping_status', Transaction::SHIPPING_IN_TRANSIT)
        );
});
