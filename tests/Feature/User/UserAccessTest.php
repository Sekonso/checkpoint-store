<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;

dataset('user roles', ['customer', 'admin']);

dataset('authenticated user routes', [
    'profile page' => ['get', '/my-mine'],
    'update profile' => ['put', '/user'],
    'cart page' => ['get', '/cart'],
    'add to cart' => ['post', '/cart'],
    'update cart item' => ['patch', '/cart/items/{cartItem}'],
    'remove cart item' => ['delete', '/cart/items/{cartItem}'],
    'clear cart' => ['delete', '/cart'],
    'transaction list' => ['get', '/transactions'],
    'checkout' => ['post', '/transactions'],
    'cancel transaction' => ['post', '/transactions/{transaction}/cancel'],
    'start payment' => ['post', '/transactions/{transaction}/payments'],
    'reset payment' => ['post', '/transactions/{transaction}/payments/reset'],
    'sync payment' => ['post', '/transactions/{transaction}/payments/sync'],
    'view transaction' => ['get', '/transactions/{transaction}'],
]);

function userAccessUser(string $role): User
{
    $factory = User::factory()->state([
        'phone' => '081234567890',
        'address' => 'Jl. Example No. 1',
    ]);

    return $role === 'admin' ? $factory->asAdmin()->create() : $factory->create();
}

function userAccessUri(string $uri, User $owner): string
{
    $product = Product::factory()->create(['stock' => 10]);

    $cartItem = CartItem::create([
        'cart_id' => Cart::create(['user_id' => $owner->id])->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $transaction = Transaction::factory()->for($owner)->withAmount(250000)->create();

    return str_replace(
        ['{cartItem}', '{transaction}'],
        [(string) $cartItem->id, $transaction->invoice_number],
        $uri,
    );
}

it('redirects guests to sign in on every authenticated route', function (string $method, string $uri) {
    $owner = User::factory()->create();

    $this->{$method}(userAccessUri($uri, $owner))->assertRedirect('/sign-in');
})->with('authenticated user routes');

it('allows a :role through every authenticated route', function (string $role, string $method, string $uri) {
    $owner = User::factory()->create();

    $response = $this->actingAs(userAccessUser($role))
        ->{$method}(userAccessUri($uri, $owner));

    expect($response->status())->toBeIn([200, 302, 404]);

    if ($response->isRedirect()) {
        expect((string) $response->headers->get('Location'))->not->toContain('/sign-in');
    }
})->with('user roles')->with('authenticated user routes');
