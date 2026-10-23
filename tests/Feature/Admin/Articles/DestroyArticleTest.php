<?php

use Illuminate\Support\Facades\Storage;
use Tests\Support\ArticleFixtures;
use Tests\Support\UserFixtures;

it('deletes an article with its tags and featured image', function () {
    Storage::fake('public');
    Storage::disk('public')->put('articles/featured/old.jpg', 'old');

    $admin = UserFixtures::admin();
    $article = ArticleFixtures::articleFor($admin, ['featured_image' => 'old.jpg']);

    $article->tags()->sync(ArticleFixtures::tags()->pluck('id'));

    $this->actingAs($admin)
        ->from('/admin/articles')
        ->delete("/admin/articles/{$article->id}")
        ->assertRedirect('/admin/articles');

    $this->assertModelMissing($article);
    $this->assertDatabaseMissing('article_tag_relations', ['article_id' => $article->id]);

    Storage::disk('public')->assertMissing('articles/featured/old.jpg');
});

it('deletes an article without a featured image', function () {
    $admin = UserFixtures::admin();
    $article = ArticleFixtures::articleFor($admin, ['featured_image' => null]);

    $this->actingAs($admin)
        ->from('/admin/articles')
        ->delete("/admin/articles/{$article->id}")
        ->assertRedirect('/admin/articles');

    $this->assertModelMissing($article);
});

it('returns 404 for an unknown article', function () {
    $this->actingAs(UserFixtures::admin())
        ->delete('/admin/articles/999999')
        ->assertNotFound();
});
