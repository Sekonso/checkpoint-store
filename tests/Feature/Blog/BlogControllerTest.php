<?php

use App\Models\Article;
use App\Models\ArticleTag;
use Inertia\Testing\AssertableInertia as Assert;

describe('index', function () {
    it('renders only published articles for guests', function () {
        $published = Article::factory()->published()->create(['title' => 'Published Article']);
        Article::factory()->draft()->create(['title' => 'Draft Article']);
        Article::factory()->archived()->create(['title' => 'Archived Article']);

        $this->get('/blog')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Blog/index')
                ->where('querySearch', null)
                ->has('articles.data', 1)
                ->where('articles.data.0.id', $published->id)
                ->where('articles.total', 1)
            );
    });

    it('paginates published articles ten per page', function () {
        foreach (range(1, 11) as $index) {
            Article::factory()->published()->create(['title' => "Article {$index}"]);
        }

        $this->get('/blog')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('articles.data', 10)
                ->where('articles.total', 11)
                ->where('articles.last_page', 2)
            );
    });

    it('filters articles by title case-insensitively', function () {
        $match = Article::factory()->published()->create(['title' => 'The Ultimate Gaming Mouse']);
        Article::factory()->published()->create(['title' => 'Mechanical Keyboard Guide']);

        $this->get('/blog?search=ultimate')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('querySearch', 'ultimate')
                ->has('articles.data', 1)
                ->where('articles.data.0.id', $match->id)
            );
    });

    it('returns an empty list when no article matches the search', function () {
        Article::factory()->published()->create(['title' => 'Mechanical Keyboard Guide']);

        $this->get('/blog?search=nonexistent')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('querySearch', 'nonexistent')
                ->has('articles.data', 0)
            );
    });

    it('lists the newest article first', function () {
        $older = Article::factory()->published()->create(['title' => 'Older Article']);

        $this->travel(1)->minutes();

        $newer = Article::factory()->published()->create(['title' => 'Newer Article']);

        $this->get('/blog')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('articles.data.0.id', $newer->id)
                ->where('articles.data.1.id', $older->id)
            );
    });

    it('preserves the search query in pagination links', function () {
        foreach (range(1, 11) as $index) {
            Article::factory()->published()->create(['title' => "Alpha Article {$index}"]);
        }

        $response = $this->get('/blog?search=alpha');

        expect($response->inertiaProps('articles.next_page_url'))->toContain('search=alpha');
    });
});

describe('show', function () {
    it('renders a published article by slug with its tags', function () {
        $article = Article::factory()->published()->create(['title' => 'Gaming Mouse Review']);
        $tags = ArticleTag::factory()->count(2)->create();
        $article->tags()->attach($tags->pluck('id')->all());

        $this->get("/blog/{$article->slug}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Blog/show')
                ->where('article.id', $article->id)
                ->where('article.slug', 'gaming-mouse-review')
                ->has('article.tags', 2)
            );
    });

    it('returns 404 for an unknown slug', function () {
        $this->get('/blog/unknown-article')->assertNotFound();
    });

    it('returns 404 when an article is requested by id', function () {
        $article = Article::factory()->published()->create();

        $this->get("/blog/{$article->id}")->assertNotFound();
    });

    it('returns 404 for a draft article', function () {
        $article = Article::factory()->draft()->create();

        $this->get("/blog/{$article->slug}")->assertNotFound();
    });

    it('returns 404 for an archived article', function () {
        $article = Article::factory()->archived()->create();

        $this->get("/blog/{$article->slug}")->assertNotFound();
    });
});
