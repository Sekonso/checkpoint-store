<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'order_id' => (string) Str::uuid(),
            'snap_token' => (string) Str::uuid(),
            'status' => Payment::STATUS_ACTIVE,
            'method' => null,
            'midtrans_transaction_id' => null,
            'amount' => 0,
            'paid_at' => null,
            'expires_at' => now()->addHour(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Payment::STATUS_ACTIVE,
        ]);
    }

    public function paid(?string $method = 'qris'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Payment::STATUS_PAID,
            'method' => $method,
            'midtrans_transaction_id' => (string) Str::uuid(),
            'paid_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Payment::STATUS_CANCELLED,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Payment::STATUS_EXPIRED,
        ]);
    }
}
