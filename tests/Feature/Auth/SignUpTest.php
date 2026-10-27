<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

it('registers and authenticates a user', function () {
    $this->post('/sign-up', [
        'name' => 'new_gamer',
        'email' => 'new-gamer@example.com',
        'password' => 'SecurePass1!',
    ])->assertRedirect('/');

    $user = User::query()->where('email', 'new-gamer@example.com')->firstOrFail();

    expect(Auth::id())->toBe($user->id)
        ->and(Hash::check('SecurePass1!', $user->password))->toBeTrue();
});

it('requires valid sign up fields', function (string $field, mixed $value) {
    $payload = [
        'name' => 'new_gamer',
        'email' => 'new-gamer@example.com',
        'password' => 'SecurePass1!',
    ];

    $payload[$field] = $value;

    $this->from('/sign-up')
        ->post('/sign-up', $payload)
        ->assertRedirect('/sign-up')
        ->assertSessionHasErrors($field);
})->with([
    'missing name' => ['name', ''],
    'invalid username' => ['name', 'new gamer'],
    'invalid email' => ['email', 'not-an-email'],
    'weak password' => ['password', 'password'],
]);

it('rejects an email already in use', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->from('/sign-up')
        ->post('/sign-up', [
            'name' => 'new_gamer',
            'email' => 'taken@example.com',
            'password' => 'SecurePass1!',
        ])
        ->assertSessionHasErrors('email');
});
