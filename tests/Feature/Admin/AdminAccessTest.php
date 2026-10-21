<?php

use App\Models\Article;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

dataset('admin routes', [
    'dashboard' => ['get', '/admin/dashboard'],
    'articles index' => ['get', '/admin/articles'],
    'articles create' => ['get', '/admin/articles/create'],
    'articles edit' => ['get', '/admin/articles/{article}/edit'],
    'articles store' => ['post', '/admin/articles'],
    'articles update' => ['put', '/admin/articles/{article}'],
    'articles archive' => ['patch', '/admin/articles/{article}/archive'],
    'articles destroy' => ['delete', '/admin/articles/{article}'],
    'products index' => ['get', '/admin/products'],
    'products create' => ['get', '/admin/products/create'],
    'products edit' => ['get', '/admin/products/{product}/edit'],
    'products store' => ['post', '/admin/products'],
    'products update' => ['put', '/admin/products/{product}'],
    'products destroy' => ['delete', '/admin/products/{product}'],
    'transactions index' => ['get', '/admin/transactions'],
    'transactions edit' => ['get', '/admin/transactions/{transaction}/edit'],
    'transactions shipping' => ['patch', '/admin/transactions/{transaction}/shipping'],
    'transactions destroy' => ['delete', '/admin/transactions/{transaction}'],
]);

function adminAccessUri(string $uri): string
{
    $admin = User::factory()->asAdmin()->create();
    $article = Article::factory()->create(['user_id' => $admin->id]);
    $product = Product::factory()->create();
    $transaction = Transaction::factory()->create();

    return str_replace(
        ['{article}', '{product}', '{transaction}'],
        [(string) $article->id, (string) $product->id, (string) $transaction->id],
        $uri,
    );
}

it('forbids guests from every admin route', function (string $method, string $uri) {
    $this->{$method}(adminAccessUri($uri))->assertForbidden();
})->with('admin routes');

it('forbids customers from every admin route', function (string $method, string $uri) {
    $this->actingAs(User::factory()->create())
        ->{$method}(adminAccessUri($uri))
        ->assertForbidden();
})->with('admin routes');

it('allows admins through every admin route', function (string $method, string $uri) {
    Storage::fake('public');

    $response = $this->actingAs(User::factory()->asAdmin()->create())
        ->{$method}(adminAccessUri($uri));

    expect($response->status())->toBeIn([200, 302]);
})->with('admin routes');
