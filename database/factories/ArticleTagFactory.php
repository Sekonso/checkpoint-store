<?php

namespace Database\Factories;

use App\Models\ArticleTag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleTag>
 */
class ArticleTagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word()
        ];
    }
}
