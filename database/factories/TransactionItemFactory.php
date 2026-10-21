<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransactionItem>
 */
class TransactionItemFactory extends Factory
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
            'product_id' => Product::factory(),
            'product_name' => fn (array $attributes) => Product::findOrFail($attributes['product_id'])->name,
            'price' => fn (array $attributes) => Product::findOrFail($attributes['product_id'])->price,
            'quantity' => fake()->numberBetween(1, 3),
            'subtotal' => fn (array $attributes) => $attributes['price'] * $attributes['quantity'],
        ];
    }
}
