<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Models\Article;
use App\Models\ArticleTag;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query_search = $request->query('search');
        $query_status = $request->query('status');

        $paginated_articles = Article::query()
            ->with('user')
            ->when($query_search, function ($query, $query_search) {
                $query->whereRaw(
                    'LOWER(title) LIKE LOWER(?)',
                    ["%{$query_search}%"]
                );
            })
            ->when($query_status, function ($query) use ($query_status) {
                if (\in_array($query_status, ['draft', 'published', 'archived'])) {
                    $query->where('status', $query_status);
                }
            })
            ->latest('created_at')
            ->paginate(15);

        return Inertia::render('Admin/Articles/index', [
            'querySearch' => $query_search,
            'queryStatus' => $query_status,
            'paginatedArticles' => $paginated_articles,
        ]);
    }

    public function create()
    {
        $tags_options = ArticleTag::select('id', 'name')
            ->get()
            ->map(fn($tag) => [
                'name' => $tag->name,
                'value' => (string) $tag->id,
            ]);

        $status_options = collect(['draft', 'published', 'archived'])->map(fn($status) => [
            'name' => $status,
            'value' => (string) $status,
        ]);

        return Inertia::render('Admin/Articles/create', [
            'tagOptions' => $tags_options,
            'statusOptions' => $status_options,
        ]);
    }

    public function store(StoreArticleRequest $request)
    {
        $validated = $request->validated();
        $author = Auth::user();
        $storedPath = null;

        try {
            DB::transaction(function () use ($request, $author, $validated, &$storedPath) {
                $article = Article::create([
                    'title' => $validated['title'],
                    'user_id' => $author->id,
                    'featured_image' => null,
                    'content' => $validated['content'],
                    'status' => $validated['status'],
                ]);

                // Storing tag
                $article->tags()->sync($validated['tags']);

                // Storing file
                $file = $request->file('featured_image');

                $storedPath = $file->store('articles/featured', 'public');

                $article->update([
                    'featured_image' => basename($storedPath),
                ]);
            });

            // Success
            return redirect("/admin/articles")->with(
                'toast',
                [
                    'type' => 'success',
                    'message' => 'New article created successfully'
                ]
            );
        } catch (\Throwable $e) {
            report($e);

            if ($storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            if (app()->environment(['local', 'development'])) {
                throw $e;
            }

            return back()
                ->withInput()
                ->with(
                    'form_error',
                    'Server failed to save your article.'
                );
        }
    }

    public function show(Article $article)
    {
        //
    }

    public function edit(Article $article)
    {
        $tags = $article->tags()->get();

        $tag_options = ArticleTag::select('id', 'name')
            ->get()
            ->map(fn($tag) => [
                'name' => $tag->name,
                'value' => (string) $tag->id,
            ]);

        $status_options = collect(['draft', 'published', 'archived'])->map(fn($status) => [
            'name' => $status,
            'value' => (string) $status,
        ]);

        return Inertia::render('Admin/Articles/edit', [
            'article' => $article,
            'tags' => $tags,
            'tagOptions' => $tag_options,
            'statusOptions' => $status_options,
        ]);
    }

    public function update(UpdateArticleRequest $request, Article $article)
    {
        $validated = $request->validated();
        $storedPath = null;

        try {
            DB::transaction(function () use ($request, $validated, $article, &$storedPath) {
                $article->update([
                    'title' => $validated['title'],
                    'content' => $validated['content'],
                    'status' => $validated['status'],
                ]);

                // Updating tag
                $article->tags()->sync($validated['tags']);

                // Updating file
                if ($request->hasFile('featured_image')) {
                    $oldImage = $article->featured_image;

                    $file = $request->file('featured_image');

                    $storedPath = $file->store('articles/featured', 'public');

                    $article->update([
                        'featured_image' => basename($storedPath),
                    ]);

                    if ($oldImage) {
                        Storage::disk('public')->delete("articles/featured/{oldImage}");
                    }
                }
            });

            // Success
            return back()->with(
                'toast',
                [
                    'type' => 'success',
                    'message' => 'Article edited successfully'
                ]
            );
        } catch (\Throwable $e) {
            report($e);

            if ($storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            if (app()->environment(['local', 'development'])) {
                throw $e;
            }

            return back()
                ->withInput()
                ->with(
                    'form_error',
                    'Server failed to save your article.'
                );
        }
    }

    public function archive(Article $article)
    {
        $article->update([
            'status' => 'archived',
        ]);

        return back();
    }

    public function destroy(Article $article)
    {
        $image_filename = $article->featured_image;

        try {
            // Delete article data
            DB::transaction(function () use ($article) {
                $article->delete();
            });

            // Delete file
            Storage::disk('public')->delete(
                "articles/featured/{$image_filename}"
            );

            // Success
            return back();
        } catch (\Throwable $e) {
            report($e);

            if (app()->environment(['local', 'development'])) {
                throw $e;
            }

            throw ValidationException::withMessages([
                'delete' => 'Failed to delete product.',
            ]);
        }
    }
}
