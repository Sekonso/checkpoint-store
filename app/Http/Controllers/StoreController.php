<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $query_search = $request->query('search');
        $query_category = $request->query('category');

        $products = Product::query()
            ->with(['images', 'category'])
            ->where('in_display', true)
            ->where('stock', '>', 0)
            ->when(
                $query_search,
                fn($query, $query_search) =>
                $query->where('name', 'like', "%{$query_search}%")
            )
            ->when(
                $query_category,
                fn($query, $query_category) =>
                $query->where('category_id', $query_category)
            )
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = ProductCategory::query()->orderBy('name')->get();

        return Inertia::render('Store/index', [
            'querySearch' => $query_search,
            'queryCategory' => $query_category,
            'products' => $products,
            'categories' => $categories
        ]);
    }

    public function show(Product $product)
    {
        abort_unless($product->in_display, 404);
        $product->load(['images', 'category']);

        return Inertia::render('Store/show', ['product' => $product]);
    }
}
