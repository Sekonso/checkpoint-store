<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Models\ArticleTag;
use App\Services\ArticleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ArticleController extends Controller
{
    public function __construct(private readonly ArticleService $article) {}

    public function index(Request $request)
    {
        $query_search = $request->query('search');
        $query_status = $request->query('status');

        return Inertia::render('Admin/Articles/index', [
            'querySearch' => $query_search,
            'queryStatus' => $query_status,
            'paginatedArticles' => $this->article->paginateForAdmin($query_search, $query_status),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Articles/create', [
            'tagOptions' => $this->tagOptions(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(StoreArticleRequest $request)
    {
        $author = Auth::user();

        try {
            $this->article->create(
                $request->safe()->only(['title', 'content', 'status', 'tags']),
                $author->id,
                $request->file('featured_image'),
            );

            return redirect('/admin/articles')->with(
                'toast',
                [
                    'type' => 'success',
                    'message' => 'New article created successfully',
                ]
            );
        } catch (\Throwable $e) {
            report($e);

            if (app()->hasDebugModeEnabled()) {
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
        return Inertia::render('Admin/Articles/edit', [
            'article' => $article,
            'tags' => $article->tags()->get(),
            'tagOptions' => $this->tagOptions(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(UpdateArticleRequest $request, Article $article)
    {
        try {
            $this->article->update(
                $article,
                $request->safe()->only(['title', 'content', 'status', 'tags']),
                $request->file('featured_image'),
            );

            return back()->with(
                'toast',
                [
                    'type' => 'success',
                    'message' => 'Article edited successfully',
                ]
            );
        } catch (\Throwable $e) {
            report($e);

            if (app()->hasDebugModeEnabled()) {
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
        $this->article->archive($article);

        return back();
    }

    public function destroy(Article $article)
    {
        try {
            $this->article->delete($article);

            return back();
        } catch (\Throwable $e) {
            report($e);

            if (app()->hasDebugModeEnabled()) {
                throw $e;
            }

            throw ValidationException::withMessages([
                'delete' => 'Failed to delete product.',
            ]);
        }
    }

    private function tagOptions(): array
    {
        return ArticleTag::select('id', 'name')
            ->get()
            ->map(fn ($tag) => [
                'name' => $tag->name,
                'value' => (string) $tag->id,
            ])
            ->all();
    }

    private function statusOptions(): array
    {
        return collect(['draft', 'published', 'archived'])->map(fn ($status) => [
            'name' => $status,
            'value' => (string) $status,
        ])->all();
    }
}
