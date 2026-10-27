<?php

use App\Models\Article;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Support\ArticleFixtures;
use Tests\Support\UserFixtures;

it('creates an article with tags and a featured image', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $payload = ArticleFixtures::payload();

    $this->actingAs($admin)
        ->post('/admin/articles', $payload)
        ->assertRedirect('/admin/articles')
        ->assertSessionHas('toast');

    $article = Article::query()->where('title', $payload['title'])->firstOrFail();

    expect($article->user_id)->toBe($admin->id)
        ->and($article->content)->toBe($payload['content'])
        ->and($article->status)->toBe('draft')
        ->and($article->slug)->toBe(Str::slug($payload['title']))
        ->and($article->tags->pluck('id')->all())->toEqualCanonicalizing($payload['tags']);

    Storage::disk('public')->assertExists("articles/featured/{$article->featured_image}");
});

it('sets published_at only when the article is published', function (string $status, bool $isPublished) {
    $this->freezeTime();
    Storage::fake('public');

    $admin = UserFixtures::admin();
    $payload = ArticleFixtures::payload(['status' => $status]);

    $this->actingAs($admin)
        ->post('/admin/articles', $payload)
        ->assertRedirect('/admin/articles');

    $article = Article::query()->where('title', $payload['title'])->firstOrFail();

    if ($isPublished) {
        expect($article->published_at?->toDateTimeString())->toBe(now()->toDateTimeString());
    } else {
        expect($article->published_at)->toBeNull();
    }
})->with([
    'draft' => ['draft', false],
    'published' => ['published', true],
    'archived' => ['archived', false],
]);

it('appends a suffix when different titles produce the same slug', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();

    $this->actingAs($admin)
        ->post('/admin/articles', ArticleFixtures::payload(['title' => 'Gaming Mouse!']))
        ->assertRedirect('/admin/articles');

    $this->actingAs($admin)
        ->post('/admin/articles', ArticleFixtures::payload(['title' => 'Gaming Mouse?']))
        ->assertRedirect('/admin/articles');

    expect(Article::query()->where('slug', 'gaming-mouse')->exists())->toBeTrue()
        ->and(Article::query()->where('slug', 'gaming-mouse-2')->exists())->toBeTrue();
});

it('requires the article fields', function () {
    $this->actingAs(UserFixtures::admin())
        ->from('/admin/articles/create')
        ->post('/admin/articles', [])
        ->assertRedirect('/admin/articles/create')
        ->assertSessionHasErrors(['title', 'tags', 'status', 'featured_image', 'content']);
});

it('rejects a duplicate title', function () {
    Storage::fake('public');

    $admin = UserFixtures::admin();

    ArticleFixtures::articleFor($admin, ['title' => 'Duplicate Title']);

    $this->actingAs($admin)
        ->from('/admin/articles/create')
        ->post('/admin/articles', ArticleFixtures::payload(['title' => 'Duplicate Title']))
        ->assertSessionHasErrors('title');
});

it('rejects tags that do not exist', function () {
    Storage::fake('public');

    $this->actingAs(UserFixtures::admin())
        ->from('/admin/articles/create')
        ->post('/admin/articles', ArticleFixtures::payload(['tags' => [999999]]))
        ->assertSessionHasErrors('tags.0');
});

it('rejects an invalid status', function () {
    Storage::fake('public');

    $this->actingAs(UserFixtures::admin())
        ->from('/admin/articles/create')
        ->post('/admin/articles', ArticleFixtures::payload(['status' => 'invalid']))
        ->assertSessionHasErrors('status');
});

it('rejects a featured image that is not an image', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $this->actingAs(UserFixtures::admin())
        ->from('/admin/articles/create')
        ->post('/admin/articles', ArticleFixtures::payload(['featured_image' => $file]))
        ->assertSessionHasErrors('featured_image');
});

it('rejects a featured image larger than two megabytes', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('cover.jpg')->size(3000);

    $this->actingAs(UserFixtures::admin())
        ->from('/admin/articles/create')
        ->post('/admin/articles', ArticleFixtures::payload(['featured_image' => $file]))
        ->assertSessionHasErrors('featured_image');
});
