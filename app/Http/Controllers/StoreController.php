<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoreController extends Controller
{
    public function __construct(private readonly ProductService $product) {}

    public function index(Request $request)
    {
        $query_search = $request->query('search');
        $query_category = $request->query('category');

        return Inertia::render('Store/index', [
            'querySearch' => $query_search,
            'queryCategory' => $query_category,
            'products' => $this->product->paginateForStore($query_search, $query_category),
            'categories' => $this->product->categories(),
        ]);
    }

    public function show(Product $product)
    {
        $product = $this->product->getDisplayableProduct($product);

        abort_unless($product, 404);

        return Inertia::render('Store/show', ['product' => $product]);
    }
}
