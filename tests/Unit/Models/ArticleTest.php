<?php

use App\Models\Article;
use App\Models\ArticleTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('generates a slug from the title', function () {
    $article = new Article(['title' => 'Top 10 Gaming Mice: 2026 Edition!']);

    expect($article->slug)->toBe('top-10-gaming-mice-2026-edition');
});

it('regenerates the slug when the title changes', function () {
    $article = Article::factory()->draft()->create(['title' => 'Original Title']);

    $article->update(['title' => 'A Different Title']);

    expect($article->fresh()->slug)->toBe('a-different-title');
});

it('sets published_at when an article is published', function () {
    $article = Article::factory()->published()->create();

    expect($article->published_at)->not->toBeNull();
});

it('clears published_at when an article is not published', function (string $status) {
    $article = Article::factory()->published()->create();

    $article->update(['status' => $status]);

    expect($article->fresh()->published_at)->toBeNull();
})->with(['draft', 'archived']);

it('reads tags through the article tag relation', function () {
    $article = Article::factory()->draft()->create();
    $tags = ArticleTag::factory()->count(2)->create();

    $article->tags()->attach($tags->pluck('id')->all());

    expect($article->fresh()->tags->pluck('id')->sort()->values()->all())
        ->toBe($tags->pluck('id')->sort()->values()->all());
});
