<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('updates the authenticated user profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/user', [
            'name' => 'updated_gamer',
            'email' => 'updated-gamer@example.com',
            'phone' => '081234567890',
            'address' => 'Jakarta',
            'password' => '',
        ])
        ->assertRedirect('/my-mine')
        ->assertSessionHas('toast', [
            'type' => 'success',
            'message' => 'Profile updated successfully.',
        ]);

    $user->refresh();

    expect($user->name)->toBe('updated_gamer')
        ->and($user->email)->toBe('updated-gamer@example.com')
        ->and($user->phone)->toBe('081234567890')
        ->and($user->address)->toBe('Jakarta')
        ->and(Hash::check('password', $user->password))->toBeTrue();
});

it('allows the user to retain their current email address', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/user', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => null,
            'address' => null,
            'password' => null,
        ])
        ->assertRedirect('/my-mine')
        ->assertSessionHas('toast');
});

it('rejects an email used by another user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user)
        ->from('/my-mine')
        ->put('/user', [
            'name' => $user->name,
            'email' => $otherUser->email,
            'phone' => null,
            'address' => null,
            'password' => null,
        ])
        ->assertRedirect('/my-mine')
        ->assertSessionHasErrors('email');
});

it('rejects missing required profile fields', function (string $field) {
    $user = User::factory()->create();
    $payload = [
        'name' => $user->name,
        'email' => $user->email,
        'phone' => null,
        'address' => null,
        'password' => null,
    ];
    $payload[$field] = '';

    $this->actingAs($user)
        ->from('/my-mine')
        ->put('/user', $payload)
        ->assertRedirect('/my-mine')
        ->assertSessionHasErrors($field);
})->with(['name', 'email']);

it('rejects a weak password update', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/my-mine')
        ->put('/user', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => null,
            'address' => null,
            'password' => 'weak',
        ])
        ->assertRedirect('/my-mine')
        ->assertSessionHasErrors('password');
});

it('hashes a valid password update', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/user', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => null,
            'address' => null,
            'password' => 'NewSecure1!',
        ])
        ->assertRedirect('/my-mine');

    expect(Hash::check('NewSecure1!', $user->fresh()->password))->toBeTrue();
});
