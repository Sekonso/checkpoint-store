<?php

use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\ArticleFixtures;
use Tests\Support\UserFixtures;

it('shows a published article with its tags', function () {
    $admin = UserFixtures::admin();
    $article = ArticleFixtures::articleFor($admin, ['status' => 'published']);

    $article->tags()->sync(ArticleFixtures::tags()->pluck('id'));

    $this->get("/blog/{$article->slug}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Blog/show')
            ->where('article.id', $article->id)
            ->has('article.tags', 2)
        );
});

it('returns 404 for an article that is not published', function (string $status) {
    $admin = UserFixtures::admin();
    $article = ArticleFixtures::articleFor($admin, ['status' => $status]);

    $this->get("/blog/{$article->slug}")->assertNotFound();
})->with([
    'draft' => ['draft'],
    'archived' => ['archived'],
]);

it('returns 404 for an unknown article', function () {
    $this->get('/blog/unknown-slug')->assertNotFound();
});
