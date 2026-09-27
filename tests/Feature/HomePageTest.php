<?php

use App\Models\Article;
use App\Models\ArticleTag;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    ProductCategory::factory()->count(3)->create();
    ArticleTag::factory()->count(3)->create();
});

it('caps the homepage products and articles at three each', function () {
    Product::factory()->withCategory()->display()->count(5)->create();
    Article::factory()->withTags()->count(5)->create(['status' => 'published']);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Home')
            ->has('latestProducts', 3)
            ->has('latestArticles', 3)
        );
});

it('only surfaces displayable products and published articles', function () {
    $visible = Product::factory()->withCategory()->display()->create();
    Product::factory()->withCategory()->create();
    Product::factory()->withCategory()->display()->create(['stock' => 0]);

    $published = Article::factory()->withTags()->create(['status' => 'published']);
    Article::factory()->withTags()->create(['status' => 'draft']);
    Article::factory()->withTags()->create(['status' => 'archived']);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Home')
            ->has('latestProducts', 1)
            ->where('latestProducts.0.id', $visible->id)
            ->where('latestProducts.0.images', [])
            ->has('latestProducts.0.category')
            ->has('latestArticles', 1)
            ->where('latestArticles.0.id', $published->id)
            ->where('latestArticles.0.slug', $published->slug)
        );
});

it('serves the homepage with empty sections when there is nothing to show', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Home')
            ->has('latestProducts', 0)
            ->has('latestArticles', 0)
        );
});
