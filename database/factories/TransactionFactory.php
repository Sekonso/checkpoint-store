<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'phone' => fake()->numerify('08##########'),
            'address' => fake()->streetAddress(),
            'total_amount' => 0,
            'status' => Transaction::STATUS_PENDING,
            'payment_type' => null,
            'paid_at' => null,
            'expires_at' => now()->addHours(PaymentService::TRANSACTION_TTL_HOURS),
            'is_complete' => false,
            'shipping_status' => Transaction::SHIPPING_IDLE,
        ];
    }

    /**
     * Set the transaction total to a specific amount.
     */
    public function withAmount(int $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'total_amount' => $amount,
        ]);
    }

    /**
     * Set the transaction total to a random amount.
     */
    public function randomAmount(int $min = 50000, int $max = 2000000): static
    {
        return $this->state(fn (array $attributes) => [
            'total_amount' => fake()->numberBetween($min, $max),
        ]);
    }

    /**
     * Add transaction items and recalculate the total from their subtotals.
     */
    public function withItems(int $count = 1): static
    {
        return $this->has(TransactionItem::factory()->count($count), 'items')
            ->afterCreating(function (Transaction $transaction) {
                $previousTotal = $transaction->total_amount;
                $total = (int) $transaction->items()->sum('subtotal');

                $transaction->payments()
                    ->where('amount', $previousTotal)
                    ->update(['amount' => $total]);

                $transaction->update(['total_amount' => $total]);
            });
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Transaction::STATUS_PENDING,
            'payment_type' => null,
            'paid_at' => null,
            'is_complete' => false,
            'shipping_status' => Transaction::SHIPPING_IDLE,
        ]);
    }

    /**
     * Mark the transaction as paid and create the matching paid payment row.
     */
    public function paid(?string $paymentType = 'qris'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Transaction::STATUS_PAID,
            'payment_type' => $paymentType,
            'paid_at' => now(),
            'is_complete' => true,
        ])->withPaidPayment([
            'method' => $paymentType,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Transaction::STATUS_CANCELLED,
            'payment_type' => null,
            'paid_at' => null,
            'is_complete' => true,
            'shipping_status' => Transaction::SHIPPING_IDLE,
        ]);
    }

    public function packaging(): static
    {
        return $this->paid()->state(fn (array $attributes) => [
            'shipping_status' => Transaction::SHIPPING_PACKAGING,
        ]);
    }

    public function inTransit(): static
    {
        return $this->paid()->state(fn (array $attributes) => [
            'shipping_status' => Transaction::SHIPPING_IN_TRANSIT,
        ]);
    }

    public function delivered(): static
    {
        return $this->paid()->state(fn (array $attributes) => [
            'shipping_status' => Transaction::SHIPPING_DELIVERED,
        ]);
    }

    /**
     * Create an active payment session for the transaction.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function withActivePayment(array $attributes = []): static
    {
        return $this->withPayment('active', $attributes);
    }

    /**
     * Create a settled payment row for the transaction.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function withPaidPayment(array $attributes = []): static
    {
        return $this->withPayment('paid', $attributes);
    }

    /**
     * Create a cancelled payment row for the transaction.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function withCancelledPayment(array $attributes = []): static
    {
        return $this->withPayment('cancelled', $attributes);
    }

    /**
     * Create an expired payment row for the transaction.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function withExpiredPayment(array $attributes = []): static
    {
        return $this->withPayment('expired', $attributes);
    }

    /**
     * Create a payment row in the given state for the transaction.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function withPayment(string $state, array $attributes = []): static
    {
        return $this->afterCreating(function (Transaction $transaction) use ($state, $attributes) {
            Payment::factory()
                ->for($transaction)
                ->{$state}()
                ->create(array_merge([
                    'amount' => $transaction->total_amount,
                    'expires_at' => $transaction->expires_at ?? now()->addHours(PaymentService::TRANSACTION_TTL_HOURS),
                ], $attributes));
        });
    }
}
