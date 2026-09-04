<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->paragraphs(5, true),
            'price' => fake()->numberBetween(50000, 500000),
            'stock' => fake()->numberBetween(5, 25),
        ];
    }

    public function display(): static
    {
        return $this->state(fn(array $attributes) => [
            'in_display' => true,
        ]);
    }

    public function withCategory(): static
    {
        $categories = ProductCategory::all();

        return $this->state(fn() => [
            'category_id' => $categories->random()->id,
        ]);
    }

    public function withImages(): static
    {
        return $this->afterCreating(function (Product $product) {
            for ($i = 1; $i <= 3; $i++) {
                $product->images()->create([
                    'filename' => 'sample.webp',
                    'order' => $i,
                ]);
            }
        });
    }
}
