<?php

use Tests\Support\ArticleFixtures;
use Tests\Support\UserFixtures;

it('archives a published article and clears its publication date', function () {
    $admin = UserFixtures::admin();
    $article = ArticleFixtures::articleFor($admin, ['status' => 'published']);

    expect($article->published_at)->not->toBeNull();

    $this->actingAs($admin)
        ->from('/admin/articles')
        ->patch("/admin/articles/{$article->id}/archive")
        ->assertRedirect('/admin/articles');

    $article->refresh();

    expect($article->status)->toBe('archived')
        ->and($article->published_at)->toBeNull();
});

it('archives a draft article', function () {
    $admin = UserFixtures::admin();
    $article = ArticleFixtures::articleFor($admin, ['status' => 'draft']);

    $this->actingAs($admin)
        ->from('/admin/articles')
        ->patch("/admin/articles/{$article->id}/archive")
        ->assertRedirect('/admin/articles');

    expect($article->refresh()->status)->toBe('archived');
});

it('returns 404 for an unknown article', function () {
    $this->actingAs(UserFixtures::admin())
        ->patch('/admin/articles/999999/archive')
        ->assertNotFound();
});
