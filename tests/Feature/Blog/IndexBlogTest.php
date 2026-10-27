<?php

use App\Models\Article;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\ArticleFixtures;
use Tests\Support\UserFixtures;

it('lists only published articles', function () {
    $admin = UserFixtures::admin();

    $published = ArticleFixtures::articleFor($admin, ['status' => 'published']);
    ArticleFixtures::articleFor($admin, ['status' => 'draft']);
    ArticleFixtures::articleFor($admin, ['status' => 'archived']);

    $this->get('/blog')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Blog/index')
            ->where('querySearch', null)
            ->has('articles.data', 1)
            ->where('articles.data.0.id', $published->id)
            ->where('articles.total', 1)
        );
});

it('filters published articles by title search', function () {
    $admin = UserFixtures::admin();

    $match = ArticleFixtures::articleFor($admin, [
        'title' => 'Gaming Mouse Review',
        'status' => 'published',
    ]);
    ArticleFixtures::articleFor($admin, [
        'title' => 'Mechanical Keyboard Guide',
        'status' => 'published',
    ]);

    $this->get('/blog?search=gaming')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('querySearch', 'gaming')
            ->has('articles.data', 1)
            ->where('articles.data.0.id', $match->id)
        );
});

it('paginates published articles ten per page', function () {
    $admin = UserFixtures::admin();

    Article::factory()->for($admin)->count(11)->published()->create();

    $this->get('/blog')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('articles.data', 10)
            ->where('articles.total', 11)
        );
});
