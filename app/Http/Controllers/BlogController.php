<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Article;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query_search = $request->query('search');

        $articles = Article::query()
            ->where('status', 'published')
            ->when($query_search, function ($query, $query_search) {
                $query->whereRaw(
                    'LOWER(title) LIKE LOWER(?)',
                    ["%{$query_search}%"]
                );
            })
            ->latest('created_at')
            ->paginate(10);

        return Inertia::render('Blog/index', [
            'querySearch' => $query_search,
            'articles' => $articles
        ]);
    }

    public function show(Request $request)
    {
        $slug = $request->route('slug');

        $article = Article::query()
            ->with('tags')
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('Blog/show', [
            'article' => $article
        ]);
    }
}
