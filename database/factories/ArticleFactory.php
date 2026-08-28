<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
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
            'title' => fake()->sentence(6),
            'featured_image' => 'sample_article.webp',
            'content' => fake()->paragraphs(5, true),
            'status' => fake()->randomElement([
                'draft',
                'published',
                'archived',
            ]),
        ];
    }
}
