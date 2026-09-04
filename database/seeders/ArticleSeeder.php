<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\ArticleTag;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ArticleTag::factory()->count(10)->create();

        Article::factory()
            ->count(100)
            ->withTags()
            ->create();
    }
}