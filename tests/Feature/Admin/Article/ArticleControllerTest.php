<?php

use App\Models\Article;
use App\Models\ArticleTag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

function articleTestAdmin(): User
{
    return User::factory()->asAdmin()->create();
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function articleTestPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'A Brand New Article',
        'content' => '<p>Article content</p>',
        'status' => 'draft',
        'tags' => [ArticleTag::factory()->create()->id],
        'featured_image' => UploadedFile::fake()->image('featured.jpg'),
    ], $overrides);
}

describe('index', function () {
    it('renders a paginated list of articles with their authors', function () {
        $admin = articleTestAdmin();
        $article = Article::factory()->published()->create([
            'user_id' => $admin->id,
            'title' => 'First Article',
        ]);

        $this->actingAs($admin)
            ->get('/admin/articles')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Articles/index')
                ->where('querySearch', null)
                ->where('queryStatus', null)
                ->has('paginatedArticles.data', 1)
                ->where('paginatedArticles.data.0.id', $article->id)
                ->where('paginatedArticles.data.0.user.name', $admin->name)
            );
    });

    it('filters articles by status', function (string $status) {
        $admin = articleTestAdmin();
        $matching = Article::factory()->{$status}()->create(['user_id' => $admin->id]);

        collect(['draft', 'published', 'archived'])
            ->reject(fn (string $value) => $value === $status)
            ->each(fn (string $otherStatus) => Article::factory()->{$otherStatus}()->create(['user_id' => $admin->id]));

        $this->actingAs($admin)
            ->get("/admin/articles?status={$status}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('queryStatus', $status)
                ->has('paginatedArticles.data', 1)
                ->where('paginatedArticles.data.0.id', $matching->id)
            );
    })->with(['draft', 'published', 'archived']);

    it('ignores an unknown status filter', function () {
        $admin = articleTestAdmin();
        Article::factory()->draft()->create(['user_id' => $admin->id]);
        Article::factory()->published()->create(['user_id' => $admin->id]);

        $this->actingAs($admin)
            ->get('/admin/articles?status=pending')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('queryStatus', 'pending')
                ->has('paginatedArticles.data', 2)
            );
    });

    it('filters articles by title case-insensitively', function () {
        $admin = articleTestAdmin();
        $match = Article::factory()->published()->create([
            'user_id' => $admin->id,
            'title' => 'Unique Gaming Keyboard',
        ]);
        Article::factory()->published()->create([
            'user_id' => $admin->id,
            'title' => 'Mechanical Mouse',
        ]);

        $this->actingAs($admin)
            ->get('/admin/articles?search=unique')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('querySearch', 'unique')
                ->has('paginatedArticles.data', 1)
                ->where('paginatedArticles.data.0.id', $match->id)
            );
    });

    it('paginates fifteen articles per page', function () {
        $admin = articleTestAdmin();

        foreach (range(1, 16) as $index) {
            Article::factory()->published()->create([
                'user_id' => $admin->id,
                'title' => "Article {$index}",
            ]);
        }

        $this->actingAs($admin)
            ->get('/admin/articles')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('paginatedArticles.data', 15)
                ->where('paginatedArticles.total', 16)
            );
    });

    it('lists the newest article first', function () {
        $admin = articleTestAdmin();
        $older = Article::factory()->published()->create([
            'user_id' => $admin->id,
            'title' => 'Older Article',
        ]);

        $this->travel(1)->minutes();

        $newer = Article::factory()->published()->create([
            'user_id' => $admin->id,
            'title' => 'Newer Article',
        ]);

        $this->actingAs($admin)
            ->get('/admin/articles')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('paginatedArticles.data.0.id', $newer->id)
                ->where('paginatedArticles.data.1.id', $older->id)
            );
    });
});

describe('create', function () {
    it('provides tag and status options', function () {
        $admin = articleTestAdmin();
        ArticleTag::factory()->count(2)->create();

        $this->actingAs($admin)
            ->get('/admin/articles/create')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Articles/create')
                ->has('tagOptions', 2)
                ->has('tagOptions.0.name')
                ->has('tagOptions.0.value')
                ->where('statusOptions', [
                    ['name' => 'draft', 'value' => 'draft'],
                    ['name' => 'published', 'value' => 'published'],
                    ['name' => 'archived', 'value' => 'archived'],
                ])
            );
    });
});

describe('store', function () {
    it('creates an article with a stored image and synced tags', function () {
        Storage::fake('public');
        $admin = articleTestAdmin();
        $tags = ArticleTag::factory()->count(2)->create();

        $response = $this->actingAs($admin)->post('/admin/articles', articleTestPayload([
            'title' => 'Top Gaming Mice of 2026',
            'content' => '<p>Our picks.</p>',
            'status' => 'published',
            'tags' => $tags->pluck('id')->all(),
        ]));

        $response->assertRedirect('/admin/articles')
            ->assertSessionHas('toast', [
                'type' => 'success',
                'message' => 'New article created successfully',
            ]);

        $article = Article::firstOrFail();

        expect($article->user_id)->toBe($admin->id)
            ->and($article->title)->toBe('Top Gaming Mice of 2026')
            ->and($article->slug)->toBe('top-gaming-mice-of-2026')
            ->and($article->content)->toBe('<p>Our picks.</p>')
            ->and($article->status)->toBe('published')
            ->and($article->published_at)->not->toBeNull()
            ->and($article->featured_image)->not->toContain('/');

        expect($article->tags()->pluck('article_tags.id')->all())
            ->toEqualCanonicalizing($tags->pluck('id')->all());

        Storage::disk('public')->assertExists("articles/featured/{$article->featured_image}");
    });

    it('sets published_at when the article is published', function () {
        Storage::fake('public');
        $admin = articleTestAdmin();

        $this->actingAs($admin)
            ->post('/admin/articles', articleTestPayload(['status' => 'published']))
            ->assertRedirect('/admin/articles');

        expect(Article::firstOrFail()->published_at)->not->toBeNull();
    });

    it('leaves published_at empty when the article is not published', function (string $status) {
        Storage::fake('public');
        $admin = articleTestAdmin();

        $this->actingAs($admin)
            ->post('/admin/articles', articleTestPayload(['status' => $status]))
            ->assertRedirect('/admin/articles');

        expect(Article::firstOrFail()->published_at)->toBeNull();
    })->with(['draft', 'archived']);

    it('uses the authenticated admin as the author', function () {
        Storage::fake('public');
        $admin = articleTestAdmin();
        $other = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->post('/admin/articles', articleTestPayload(['user_id' => $other->id]))
            ->assertRedirect('/admin/articles');

        expect(Article::firstOrFail()->user_id)->toBe($admin->id);
    });

    it('requires every article field', function () {
        Storage::fake('public');
        $admin = articleTestAdmin();

        $this->actingAs($admin)
            ->post('/admin/articles', [])
            ->assertSessionHasErrors(['title', 'tags', 'status', 'featured_image', 'content']);

        $this->assertDatabaseCount('articles', 0);
        Storage::disk('public')->assertEmpty();
    });

    it('rejects a duplicate title', function () {
        Storage::fake('public');
        $admin = articleTestAdmin();
        Article::factory()->create(['user_id' => $admin->id, 'title' => 'Taken Title']);

        $this->actingAs($admin)
            ->post('/admin/articles', articleTestPayload(['title' => 'Taken Title']))
            ->assertSessionHasErrors(['title' => 'The title has already been taken.']);

        $this->assertDatabaseCount('articles', 1);
    });

    it('rejects a title longer than 255 characters', function () {
        Storage::fake('public');
        $admin = articleTestAdmin();

        $this->actingAs($admin)
            ->post('/admin/articles', articleTestPayload(['title' => str_repeat('a', 256)]))
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('articles', 0);
    });

    it('rejects invalid tags', function (mixed $tags, string $errorKey) {
        Storage::fake('public');
        $admin = articleTestAdmin();

        $this->actingAs($admin)
            ->post('/admin/articles', articleTestPayload(['tags' => $tags]))
            ->assertSessionHasErrors($errorKey);

        $this->assertDatabaseCount('articles', 0);
    })->with([
        'empty' => [[], 'tags'],
        'not an array' => ['one', 'tags'],
        'unknown id' => [[999999], 'tags.0'],
        'not an integer' => [['one'], 'tags.0'],
    ]);

    it('rejects an unknown status', function () {
        Storage::fake('public');
        $admin = articleTestAdmin();

        $this->actingAs($admin)
            ->post('/admin/articles', articleTestPayload(['status' => 'pending']))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseCount('articles', 0);
    });

    it('rejects an invalid featured image', function (string $type) {
        Storage::fake('public');
        $admin = articleTestAdmin();

        $image = match ($type) {
            'not an image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            'gif' => UploadedFile::fake()->image('photo.gif'),
            'too large' => UploadedFile::fake()->image('large.jpg')->size(2049),
        };

        $this->actingAs($admin)
            ->post('/admin/articles', articleTestPayload(['featured_image' => $image]))
            ->assertSessionHasErrors('featured_image');

        $this->assertDatabaseCount('articles', 0);
        Storage::disk('public')->assertEmpty();
    })->with(['not an image', 'gif', 'too large']);
});

describe('edit', function () {
    it('renders the edit page with the article and its tags', function () {
        $admin = articleTestAdmin();
        $article = Article::factory()->draft()->create([
            'user_id' => $admin->id,
            'title' => 'Editable Article',
        ]);
        $tag = ArticleTag::factory()->create();
        $article->tags()->attach($tag);

        $this->actingAs($admin)
            ->get("/admin/articles/{$article->id}/edit")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Articles/edit')
                ->where('article.id', $article->id)
                ->where('article.title', 'Editable Article')
                ->has('tags', 1)
                ->where('tags.0.id', $tag->id)
                ->has('tagOptions', 1)
                ->has('statusOptions', 3)
            );
    });

    it('returns 404 for an unknown article', function () {
        $admin = articleTestAdmin();

        $this->actingAs($admin)->get('/admin/articles/999999/edit')->assertNotFound();
    });
});

describe('update', function () {
    it('updates an article and regenerates its slug', function () {
        Storage::fake('public');
        $admin = articleTestAdmin();
        $article = Article::factory()->draft()->create([
            'user_id' => $admin->id,
            'title' => 'Original Title',
        ]);
        $oldTag = ArticleTag::factory()->create();
        $article->tags()->attach($oldTag);
        $tags = ArticleTag::factory()->count(2)->create();

        $this->actingAs($admin)
            ->from("/admin/articles/{$article->id}/edit")
            ->put("/admin/articles/{$article->id}", articleTestPayload([
                'title' => 'Updated Title',
                'content' => '<p>Updated content</p>',
                'status' => 'published',
                'tags' => $tags->pluck('id')->all(),
            ]))
            ->assertRedirect("/admin/articles/{$article->id}/edit")
            ->assertSessionHas('toast', [
                'type' => 'success',
                'message' => 'Article edited successfully',
            ]);

        $article->refresh();

        expect($article->title)->toBe('Updated Title')
            ->and($article->slug)->toBe('updated-title')
            ->and($article->content)->toBe('<p>Updated content</p>')
            ->and($article->status)->toBe('published')
            ->and($article->published_at)->not->toBeNull();

        $attachedTagIds = $article->tags()->pluck('article_tags.id')->all();

        expect($attachedTagIds)->toEqualCanonicalizing($tags->pluck('id')->all())
            ->and($attachedTagIds)->not->toContain($oldTag->id);
    });

    it('allows keeping the current title', function () {
        Storage::fake('public');
        $admin = articleTestAdmin();
        $article = Article::factory()->draft()->create([
            'user_id' => $admin->id,
            'title' => 'Keep This Title',
        ]);

        $this->actingAs($admin)
            ->from("/admin/articles/{$article->id}/edit")
            ->put("/admin/articles/{$article->id}", articleTestPayload(['title' => 'Keep This Title']))
            ->assertSessionHasNoErrors()
            ->assertRedirect("/admin/articles/{$article->id}/edit");

        expect($article->fresh()->title)->toBe('Keep This Title');
    });

    it('rejects the title of another article', function () {
        Storage::fake('public');
        $admin = articleTestAdmin();
        $article = Article::factory()->draft()->create([
            'user_id' => $admin->id,
            'title' => 'Original Title',
        ]);
        Article::factory()->create(['user_id' => $admin->id, 'title' => 'Taken Title']);

        $this->actingAs($admin)
            ->from("/admin/articles/{$article->id}/edit")
            ->put("/admin/articles/{$article->id}", articleTestPayload(['title' => 'Taken Title']))
            ->assertSessionHasErrors(['title' => 'The title has already been taken.']);

        expect($article->fresh()->title)->toBe('Original Title');
    });

    it('keeps the existing image when no new image is uploaded', function () {
        Storage::fake('public');
        Storage::disk('public')->put('articles/featured/existing.webp', 'image');
        $admin = articleTestAdmin();
        $article = Article::factory()->draft()->create([
            'user_id' => $admin->id,
            'featured_image' => 'existing.webp',
        ]);

        $payload = articleTestPayload();
        unset($payload['featured_image']);

        $this->actingAs($admin)
            ->from("/admin/articles/{$article->id}/edit")
            ->put("/admin/articles/{$article->id}", $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect("/admin/articles/{$article->id}/edit");

        expect($article->fresh()->featured_image)->toBe('existing.webp');
        Storage::disk('public')->assertExists('articles/featured/existing.webp');
    });

    it('replaces the image and deletes the old file', function () {
        Storage::fake('public');
        Storage::disk('public')->put('articles/featured/old.webp', 'old image');
        $admin = articleTestAdmin();
        $article = Article::factory()->draft()->create([
            'user_id' => $admin->id,
            'featured_image' => 'old.webp',
        ]);

        $this->actingAs($admin)
            ->from("/admin/articles/{$article->id}/edit")
            ->put("/admin/articles/{$article->id}", articleTestPayload())
            ->assertSessionHasNoErrors();

        $article->refresh();

        expect($article->featured_image)->not->toBe('old.webp');
        Storage::disk('public')->assertMissing('articles/featured/old.webp');
        Storage::disk('public')->assertExists("articles/featured/{$article->featured_image}");
    });

    it('rejects an invalid featured image', function (string $type) {
        Storage::fake('public');
        $admin = articleTestAdmin();
        $article = Article::factory()->draft()->create(['user_id' => $admin->id]);

        $image = match ($type) {
            'not an image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            'gif' => UploadedFile::fake()->image('photo.gif'),
            'too large' => UploadedFile::fake()->image('large.jpg')->size(2049),
        };

        $this->actingAs($admin)
            ->from("/admin/articles/{$article->id}/edit")
            ->put("/admin/articles/{$article->id}", articleTestPayload(['featured_image' => $image]))
            ->assertSessionHasErrors('featured_image');

        expect($article->fresh()->featured_image)->toBe('sample.webp');
        Storage::disk('public')->assertEmpty();
    })->with(['not an image', 'gif', 'too large']);

    it('rejects an unknown tag without changing the article', function () {
        Storage::fake('public');
        $admin = articleTestAdmin();
        $article = Article::factory()->draft()->create([
            'user_id' => $admin->id,
            'title' => 'Original Title',
        ]);
        $oldTag = ArticleTag::factory()->create();
        $article->tags()->attach($oldTag);

        $this->actingAs($admin)
            ->from("/admin/articles/{$article->id}/edit")
            ->put("/admin/articles/{$article->id}", articleTestPayload([
                'title' => 'Changed Title',
                'tags' => [999999],
            ]))
            ->assertSessionHasErrors('tags.0');

        $article->refresh();

        expect($article->title)->toBe('Original Title')
            ->and($article->tags()->pluck('article_tags.id')->all())->toBe([$oldTag->id]);
    });

    it('clears published_at when the status becomes archived', function () {
        Storage::fake('public');
        $admin = articleTestAdmin();
        $article = Article::factory()->published()->create(['user_id' => $admin->id]);

        expect($article->published_at)->not->toBeNull();

        $this->actingAs($admin)
            ->from("/admin/articles/{$article->id}/edit")
            ->put("/admin/articles/{$article->id}", articleTestPayload(['status' => 'archived']))
            ->assertSessionHasNoErrors();

        expect($article->fresh()->published_at)->toBeNull();
    });

    it('returns 404 for an unknown article', function () {
        Storage::fake('public');
        $admin = articleTestAdmin();

        $this->actingAs($admin)
            ->put('/admin/articles/999999', articleTestPayload())
            ->assertNotFound();
    });
});

describe('archive', function () {
    it('archives a published article and clears published_at', function () {
        $admin = articleTestAdmin();
        $article = Article::factory()->published()->create([
            'user_id' => $admin->id,
            'featured_image' => 'keep.webp',
        ]);
        $tag = ArticleTag::factory()->create();
        $article->tags()->attach($tag);

        $this->actingAs($admin)
            ->from('/admin/articles')
            ->patch("/admin/articles/{$article->id}/archive")
            ->assertRedirect('/admin/articles');

        $article->refresh();

        expect($article->status)->toBe('archived')
            ->and($article->published_at)->toBeNull()
            ->and($article->featured_image)->toBe('keep.webp');

        $this->assertDatabaseHas('article_tag_relations', [
            'article_id' => $article->id,
            'article_tag_id' => $tag->id,
        ]);
    });

    it('archives an already archived article without error', function () {
        $admin = articleTestAdmin();
        $article = Article::factory()->archived()->create(['user_id' => $admin->id]);

        $this->actingAs($admin)
            ->from('/admin/articles')
            ->patch("/admin/articles/{$article->id}/archive")
            ->assertRedirect('/admin/articles');

        expect($article->fresh()->status)->toBe('archived');
    });
});

describe('destroy', function () {
    it('deletes the article, its tags and its image', function () {
        Storage::fake('public');
        Storage::disk('public')->put('articles/featured/delete-me.jpg', 'image');
        $admin = articleTestAdmin();
        $article = Article::factory()->draft()->create([
            'user_id' => $admin->id,
            'featured_image' => 'delete-me.jpg',
        ]);
        $tag = ArticleTag::factory()->create();
        $article->tags()->attach($tag);

        $this->actingAs($admin)
            ->from('/admin/articles')
            ->delete("/admin/articles/{$article->id}")
            ->assertRedirect('/admin/articles');

        $this->assertModelMissing($article);
        $this->assertDatabaseMissing('article_tag_relations', ['article_id' => $article->id]);
        $this->assertDatabaseHas('article_tags', ['id' => $tag->id]);
        Storage::disk('public')->assertMissing('articles/featured/delete-me.jpg');
    });

    it('deletes an article without a featured image', function () {
        $admin = articleTestAdmin();
        $article = Article::factory()->draft()->create([
            'user_id' => $admin->id,
            'featured_image' => null,
        ]);

        $this->actingAs($admin)
            ->from('/admin/articles')
            ->delete("/admin/articles/{$article->id}")
            ->assertRedirect('/admin/articles');

        $this->assertModelMissing($article);
    });

    it('does not delete another article image', function () {
        Storage::fake('public');
        Storage::disk('public')->put('articles/featured/other.jpg', 'other image');
        $admin = articleTestAdmin();
        $article = Article::factory()->draft()->create([
            'user_id' => $admin->id,
            'featured_image' => 'delete-me.jpg',
        ]);
        Article::factory()->draft()->create([
            'user_id' => $admin->id,
            'featured_image' => 'other.jpg',
        ]);

        $this->actingAs($admin)
            ->from('/admin/articles')
            ->delete("/admin/articles/{$article->id}")
            ->assertRedirect('/admin/articles');

        Storage::disk('public')->assertExists('articles/featured/other.jpg');
    });

    it('returns 404 for an unknown article', function () {
        $admin = articleTestAdmin();

        $this->actingAs($admin)->delete('/admin/articles/999999')->assertNotFound();
    });
});
