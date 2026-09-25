<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function __construct(private readonly ArticleService $article) {}

    public function index(Request $request)
    {
        $query_search = $request->query('search');

        return Inertia::render('Blog/index', [
            'querySearch' => $query_search,
            'articles' => $this->article->paginatePublished($query_search),
        ]);
    }

    public function show(Article $article)
    {
        return Inertia::render('Blog/show', [
            'article' => $this->article->findWithTags($article),
        ]);
    }
}
