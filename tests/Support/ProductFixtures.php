<?php

namespace Tests\Support;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

final class ProductFixtures
{
    /**
     * Create a single product category.
     */
    public static function category(): ProductCategory
    {
        return ProductCategory::factory()->create();
    }

    /**
     * Create the given number of product categories.
     *
     * @return Collection<int, ProductCategory>
     */
    public static function categories(int $count = 2): Collection
    {
        return ProductCategory::factory()->count($count)->create();
    }

    /**
     * Build a valid product payload, applying any overrides.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function payload(array $overrides = []): array
    {
        if (! array_key_exists('category_id', $overrides)) {
            $overrides['category_id'] = self::category()->id;
        }

        return array_merge([
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(50000, 500000),
            'stock' => fake()->numberBetween(0, 25),
            'in_display' => true,
            'image-1' => UploadedFile::fake()->image('front.jpg', 640, 480),
            'image-2' => UploadedFile::fake()->image('side.jpg', 640, 480),
            'image-3' => UploadedFile::fake()->image('back.jpg', 640, 480),
        ], $overrides);
    }

    /**
     * Create a product with a category, applying any overrides.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function productFor(array $attributes = []): Product
    {
        if (! array_key_exists('category_id', $attributes)) {
            $attributes['category_id'] = self::category()->id;
        }

        return Product::factory()->create($attributes);
    }
}
