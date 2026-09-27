<?php

namespace App\Http\Controllers;

use App\Services\ArticleService;
use App\Services\ProductService;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __construct(
        private readonly ProductService $product,
        private readonly ArticleService $article
    ) {}

    public function index()
    {
        return Inertia::render('Home', [
            'latestProducts' => $this->product->latestInDisplay(3),
            'latestArticles' => $this->article->latestPublished(4),
        ]);
    }
}
