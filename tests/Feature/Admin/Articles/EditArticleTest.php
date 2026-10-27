<?php

use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\ArticleFixtures;
use Tests\Support\UserFixtures;

it('renders the article edit form with tags and options', function () {
    $admin = UserFixtures::admin();
    $tags = ArticleFixtures::tags();
    $article = ArticleFixtures::articleFor($admin, ['title' => 'Edit Me']);

    $article->tags()->sync($tags->pluck('id'));

    $this->actingAs($admin)
        ->get("/admin/articles/{$article->id}/edit")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Articles/edit')
            ->where('article.id', $article->id)
            ->has('tags', 2)
            ->has('tagOptions', 2)
            ->has('statusOptions', 3)
        );
});

it('returns 404 for an unknown article', function () {
    $this->actingAs(UserFixtures::admin())
        ->get('/admin/articles/999999/edit')
        ->assertNotFound();
});
