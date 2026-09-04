<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductCategory;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProductCategory::factory()->count(10)->create();

        Product::factory()
            ->count(10)
            ->withCategory()
            ->withImages()
            ->create();

        Product::factory()
            ->count(10)
            ->withCategory()
            ->display()
            ->withImages()
            ->create();
    }
}
