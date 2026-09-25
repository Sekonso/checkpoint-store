<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class ArticleService
{
    public function paginateForAdmin(?string $search, ?string $status): LengthAwarePaginator
    {
        return Article::query()
            ->with('user')
            ->when(
                $search,
                fn (Builder $query, string $search) => $query->whereLike('title', "%{$search}%", caseSensitive: false)
            )
            ->when(
                \in_array($status, ['draft', 'published', 'archived']),
                fn (Builder $query) => $query->where('status', $status)
            )
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();
    }

    public function paginatePublished(?string $search): LengthAwarePaginator
    {
        return Article::query()
            ->where('status', 'published')
            ->when(
                $search,
                fn (Builder $query, string $search) => $query->whereLike('title', "%{$search}%", caseSensitive: false)
            )
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();
    }

    public function findWithTags(Article $article): Article
    {
        return $article->load('tags');
    }

    public function create(array $data, int $authorId, ?UploadedFile $featuredImage): Article
    {
        $storedPath = null;

        try {
            return DB::transaction(function () use ($data, $authorId, $featuredImage, &$storedPath) {
                $article = Article::create([
                    'title' => $data['title'],
                    'user_id' => $authorId,
                    'featured_image' => null,
                    'content' => $data['content'],
                    'status' => $data['status'],
                ]);

                $article->tags()->sync($data['tags'] ?? []);

                $storedPath = $featuredImage->store('articles/featured', 'public');

                $article->update([
                    'featured_image' => basename($storedPath),
                ]);

                return $article;
            });
        } catch (Throwable $e) {
            if ($storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            throw $e;
        }
    }

    public function update(Article $article, array $data, ?UploadedFile $featuredImage = null): void
    {
        $storedPath = null;

        try {
            DB::transaction(function () use ($article, $data, $featuredImage, &$storedPath) {
                $article->update([
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'status' => $data['status'],
                ]);

                $article->tags()->sync($data['tags'] ?? []);

                if ($featuredImage) {
                    $oldImage = $article->featured_image;

                    $storedPath = $featuredImage->store('articles/featured', 'public');

                    $article->update([
                        'featured_image' => basename($storedPath),
                    ]);

                    if ($oldImage) {
                        Storage::disk('public')->delete("articles/featured/{$oldImage}");
                    }
                }
            });
        } catch (Throwable $e) {
            if ($storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            throw $e;
        }
    }

    public function archive(Article $article): void
    {
        $article->update([
            'status' => 'archived',
        ]);
    }

    public function delete(Article $article): void
    {
        $imageFilename = $article->featured_image;

        DB::transaction(fn () => $article->delete());

        if ($imageFilename) {
            Storage::disk('public')->delete("articles/featured/{$imageFilename}");
        }
    }
}
