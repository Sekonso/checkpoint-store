<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\ArticleTag;

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
            'user_id' => User::factory()->asAdmin()->createOne(),
            'title' => fake()->sentence(6),
            'content' => fake()->paragraphs(5, true),
            'featured_image' => 'sample.webp',
            'status' => fake()->randomElement([
                'draft',
                'published',
                'archived',
            ]),
        ];
    }

    public function withTags(): static
    {
        $tags = ArticleTag::all();

        return $this->afterCreating(function (Article $article) use ($tags) {
            $randomTags = $tags
                ->random(fake()->numberBetween(1, 3))
                ->pluck('id')
                ->toArray();

            $article->tags()->attach($randomTags);
        });
    }
}
