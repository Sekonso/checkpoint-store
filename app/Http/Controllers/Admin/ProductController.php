<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $product) {}

    public function index(Request $request)
    {
        $query_search = $request->query('search');
        $query_display = $request->query('display');

        return Inertia::render('Admin/Products/index', [
            'querySearch' => $query_search,
            'queryDisplay' => $query_display,
            'paginatedProducts' => $this->product->paginateForAdmin($query_search, $query_display),
        ]);
    }

    public function create()
    {
        $product_categories = ProductCategory::all();

        return Inertia::render('Admin/Products/create', [
            'productCategories' => $product_categories,
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        try {
            $this->product->create(
                $request->safe()->only(['name', 'description', 'price', 'stock', 'in_display', 'category_id']),
                $this->imagesFromRequest($request),
            );

            return redirect('/admin/products')->with(
                'toast',
                [
                    'type' => 'success',
                    'message' => 'New product created successfully',
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
                    'Server failed to save your product.'
                );
        }
    }

    public function show(string $id)
    {
        //
    }

    public function edit(Product $product)
    {
        $product_categories = ProductCategory::all();

        return Inertia::render('Admin/Products/edit', [
            'product' => $product,
            'productCategories' => $product_categories,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $this->product->update(
                $product,
                $request->safe()->only(['name', 'description', 'price', 'stock', 'in_display', 'category_id']),
                $this->imagesFromRequest($request),
            );

            return back()->with(
                'toast',
                [
                    'type' => 'success',
                    'message' => 'Product edited successfully',
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
                    'Server failed to save your product.'
                );
        }
    }

    public function destroy(Product $product)
    {
        try {
            $this->product->delete($product);

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

    private function imagesFromRequest(Request $request): array
    {
        return [
            1 => $request->file('image-1'),
            2 => $request->file('image-2'),
            3 => $request->file('image-3'),
        ];
    }
}
