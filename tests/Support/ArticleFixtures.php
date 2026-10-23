<?php

namespace Tests\Support;

use App\Models\Article;
use App\Models\ArticleTag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

final class ArticleFixtures
{
    /**
     * Create the given number of article tags.
     *
     * @return Collection<int, ArticleTag>
     */
    public static function tags(int $count = 2): Collection
    {
        return ArticleTag::factory()->count($count)->create();
    }

    /**
     * Build a valid article payload, applying any overrides.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function payload(array $overrides = []): array
    {
        $tags = $overrides['tags'] ?? self::tags()->pluck('id')->all();

        return array_merge([
            'title' => fake()->unique()->sentence(4),
            'content' => fake()->paragraph(),
            'status' => 'draft',
            'tags' => $tags,
            'featured_image' => UploadedFile::fake()->image('cover.jpg', 640, 480),
        ], $overrides, ['tags' => $tags]);
    }

    /**
     * Create an article owned by the given admin, applying any overrides.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function articleFor(User $admin, array $attributes = []): Article
    {
        return Article::factory()->for($admin)->create($attributes);
    }
}
