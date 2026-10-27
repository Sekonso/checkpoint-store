<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Tests\Support\ArticleFixtures;
use Tests\Support\UserFixtures;

it('updates the article and syncs its tags', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $article = ArticleFixtures::articleFor($admin, ['title' => 'Old Title']);

    $article->tags()->sync(ArticleFixtures::tags()->pluck('id'));

    $newTags = ArticleFixtures::tags(2);
    $payload = ArticleFixtures::payload([
        'title' => 'New Title',
        'content' => 'New content',
        'status' => 'published',
        'tags' => $newTags->pluck('id')->all(),
    ]);

    $this->actingAs($admin)
        ->from("/admin/articles/{$article->id}/edit")
        ->put("/admin/articles/{$article->id}", $payload)
        ->assertRedirect("/admin/articles/{$article->id}/edit")
        ->assertSessionHas('toast');

    $article->refresh();

    expect($article->title)->toBe('New Title')
        ->and($article->content)->toBe('New content')
        ->and($article->status)->toBe('published')
        ->and($article->tags->pluck('id')->all())->toEqualCanonicalizing($newTags->pluck('id')->all());
});

it('keeps the existing featured image when no new image is uploaded', function () {
    Storage::fake('public');
    Storage::disk('public')->put('articles/featured/old.jpg', 'old');

    $admin = UserFixtures::admin();
    $article = ArticleFixtures::articleFor($admin, ['featured_image' => 'old.jpg']);

    $payload = Arr::except(
        ArticleFixtures::payload(['title' => 'Updated Title']),
        ['featured_image'],
    );

    $this->actingAs($admin)
        ->from("/admin/articles/{$article->id}/edit")
        ->put("/admin/articles/{$article->id}", $payload)
        ->assertRedirect("/admin/articles/{$article->id}/edit");

    $article->refresh();

    expect($article->featured_image)->toBe('old.jpg');

    Storage::disk('public')->assertExists('articles/featured/old.jpg');
});

it('replaces the featured image and deletes the old file', function () {
    Storage::fake('public');
    Storage::disk('public')->put('articles/featured/old.jpg', 'old');

    $admin = UserFixtures::admin();
    $article = ArticleFixtures::articleFor($admin, ['featured_image' => 'old.jpg']);

    $payload = ArticleFixtures::payload(['title' => 'Updated Title']);

    $this->actingAs($admin)
        ->from("/admin/articles/{$article->id}/edit")
        ->put("/admin/articles/{$article->id}", $payload)
        ->assertRedirect("/admin/articles/{$article->id}/edit");

    $article->refresh();

    expect($article->featured_image)->not->toBe('old.jpg');

    Storage::disk('public')->assertMissing('articles/featured/old.jpg');
    Storage::disk('public')->assertExists("articles/featured/{$article->featured_image}");
});

it('allows keeping the current title', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $article = ArticleFixtures::articleFor($admin, ['title' => 'Same Title']);

    $payload = Arr::except(
        ArticleFixtures::payload(['title' => 'Same Title']),
        ['featured_image'],
    );

    $this->actingAs($admin)
        ->from("/admin/articles/{$article->id}/edit")
        ->put("/admin/articles/{$article->id}", $payload)
        ->assertRedirect("/admin/articles/{$article->id}/edit")
        ->assertSessionHasNoErrors();
});

it('rejects a title used by another article', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $article = ArticleFixtures::articleFor($admin, ['title' => 'First Title']);

    ArticleFixtures::articleFor($admin, ['title' => 'Second Title']);

    $payload = Arr::except(
        ArticleFixtures::payload(['title' => 'Second Title']),
        ['featured_image'],
    );

    $this->actingAs($admin)
        ->from("/admin/articles/{$article->id}/edit")
        ->put("/admin/articles/{$article->id}", $payload)
        ->assertSessionHasErrors('title');
});

it('clears published_at when the article is moved back to draft', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $article = ArticleFixtures::articleFor($admin, ['status' => 'published']);

    expect($article->published_at)->not->toBeNull();

    $payload = Arr::except(
        ArticleFixtures::payload(['title' => $article->title, 'status' => 'draft']),
        ['featured_image'],
    );

    $this->actingAs($admin)
        ->from("/admin/articles/{$article->id}/edit")
        ->put("/admin/articles/{$article->id}", $payload)
        ->assertRedirect("/admin/articles/{$article->id}/edit");

    $article->refresh();

    expect($article->status)->toBe('draft')
        ->and($article->published_at)->toBeNull();
});

it('sets published_at when the article is published', function () {
    $this->freezeTime();
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $article = ArticleFixtures::articleFor($admin, ['status' => 'draft']);

    $payload = Arr::except(
        ArticleFixtures::payload(['title' => $article->title, 'status' => 'published']),
        ['featured_image'],
    );

    $this->actingAs($admin)
        ->from("/admin/articles/{$article->id}/edit")
        ->put("/admin/articles/{$article->id}", $payload)
        ->assertRedirect("/admin/articles/{$article->id}/edit");

    $article->refresh();

    expect($article->published_at?->toDateTimeString())->toBe(now()->toDateTimeString());
});

it('validates the update payload', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $article = ArticleFixtures::articleFor($admin, ['title' => 'Valid Title']);

    $payload = Arr::except(
        ArticleFixtures::payload(['title' => 'Valid Title', 'status' => 'invalid']),
        ['featured_image'],
    );

    $this->actingAs($admin)
        ->from("/admin/articles/{$article->id}/edit")
        ->put("/admin/articles/{$article->id}", $payload)
        ->assertSessionHasErrors('status');
});

it('returns 404 for an unknown article', function () {
    $this->actingAs(UserFixtures::admin())
        ->put('/admin/articles/999999', ArticleFixtures::payload())
        ->assertNotFound();
});
