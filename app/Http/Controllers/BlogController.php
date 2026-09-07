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
            ->when(
                $query_search,
                fn($query, $query_search) =>
                $query->whereLike('title', "%{$query_search}%", caseSensitive: false)
            )
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Blog/index', [
            'querySearch' => $query_search,
            'articles' => $articles
        ]);
    }

    public function show(Article $article)
    {
        $article = $article->load('tags');

        return Inertia::render('Blog/show', [
            'article' => $article
        ]);
    }
}
