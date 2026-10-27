<?php

use App\Models\Article;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\ArticleFixtures;
use Tests\Support\UserFixtures;

it('lists the latest articles with pagination', function () {
    $admin = UserFixtures::admin();
    $now = now();

    $this->travelTo($now->copy()->subDay());
    $older = ArticleFixtures::articleFor($admin);

    $this->travelTo($now);
    $newer = ArticleFixtures::articleFor($admin);

    $this->actingAs($admin)
        ->get('/admin/articles')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Articles/index')
            ->where('querySearch', null)
            ->where('queryStatus', null)
            ->has('paginatedArticles.data', 2)
            ->where('paginatedArticles.data.0.id', $newer->id)
            ->where('paginatedArticles.data.1.id', $older->id)
        );
});

it('paginates articles fifteen per page', function () {
    $admin = UserFixtures::admin();

    Article::factory()->for($admin)->count(16)->create();

    $this->actingAs($admin)
        ->get('/admin/articles')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('paginatedArticles.data', 15)
            ->where('paginatedArticles.total', 16)
        );
});

it('filters articles by title search', function () {
    $admin = UserFixtures::admin();

    $match = ArticleFixtures::articleFor($admin, ['title' => 'Gaming Mouse Review']);
    ArticleFixtures::articleFor($admin, ['title' => 'Mechanical Keyboard Guide']);

    $this->actingAs($admin)
        ->get('/admin/articles?search=gaming')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('querySearch', 'gaming')
            ->has('paginatedArticles.data', 1)
            ->where('paginatedArticles.data.0.id', $match->id)
        );
});

it('filters articles by status', function () {
    $admin = UserFixtures::admin();

    $published = ArticleFixtures::articleFor($admin, ['status' => 'published']);
    ArticleFixtures::articleFor($admin, ['status' => 'draft']);

    $this->actingAs($admin)
        ->get('/admin/articles?status=published')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('queryStatus', 'published')
            ->has('paginatedArticles.data', 1)
            ->where('paginatedArticles.data.0.id', $published->id)
        );
});

it('ignores an invalid status filter', function () {
    $admin = UserFixtures::admin();

    ArticleFixtures::articleFor($admin, ['status' => 'published']);
    ArticleFixtures::articleFor($admin, ['status' => 'draft']);

    $this->actingAs($admin)
        ->get('/admin/articles?status=unknown')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('queryStatus', 'unknown')
            ->has('paginatedArticles.data', 2)
        );
});
