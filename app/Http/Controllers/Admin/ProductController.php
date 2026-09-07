<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query_search = $request->query('search');
        $query_display = $request->query('display');

        $paginatedProducts = Product::query()
            ->when(
                $query_search,
                fn($query, $query_search) =>
                $query->whereLike('name', "%{$query_search}%", caseSensitive: false)
            )
            ->when(
                \in_array($query_display, ['true', 'false']),
                fn($query) => $query->where('in_display', filter_var($query_display, FILTER_VALIDATE_BOOLEAN))
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Products/index', [
            'querySearch' => $query_search,
            'queryDisplay' => $query_display,
            'paginatedProducts' => $paginatedProducts,
        ]);
    }

    public function create()
    {
        $product_categories = ProductCategory::all();

        return Inertia::render('Admin/Products/create', [
            'productCategories' => $product_categories
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();
        $image_paths = [];

        try {
            DB::transaction(function () use ($request, $validated, &$image_paths) {
                // Storing attributes
                $product = Product::create([
                    'name' => $validated['name'],
                    'description' => $validated['description'],
                    'price' => $validated['price'],
                    'stock' => $validated['stock'],
                    'in_display' => $validated['in_display'],
                    'category_id' => $validated['category_id'],
                ]);

                // Storing files
                $order = 1;

                foreach (['image-1', 'image-2', 'image-3'] as $name) {
                    $image_file = $request->file($name);

                    $path = $image_file->store(
                        "products/{$product->id}",
                        'public'
                    );

                    $image_paths[] = $path;

                    $product->images()->create([
                        'filename' => basename($path),
                        'order' => $order,
                    ]);

                    $order++;
                }
            });

            // Success
            return redirect('/admin/products')->with(
                'toast',
                [
                    'type' => 'success',
                    'message' => 'New product created successfully',
                ]
            );
        } catch (\Throwable $e) {
            report($e);

            foreach ($image_paths as $path) {
                Storage::disk('public')->delete($path);
            }

            if (app()->environment(['local', 'development'])) {
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
            'productCategories' => $product_categories
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $validated = $request->validated();
        $image_paths = [];

        try {
            DB::transaction(function () use ($request, $validated, $product, &$image_paths) {
                // Updating attributes
                $product->update([
                    'name' => $validated['name'],
                    'description' => $validated['description'],
                    'price' => $validated['price'],
                    'stock' => $validated['stock'],
                    'in_display' => $validated['in_display'],
                    'category_id' => $validated['category_id'],
                ]);

                // Updating files
                foreach ([1, 2, 3] as $order) {
                    if (!$request->hasFile("image-{$order}")) {
                        continue;
                    }

                    $imageFile = $request->file("image-{$order}");

                    $productImage = $product->images()
                        ->where('order', $order)
                        ->first();

                    // If image exist
                    if ($productImage) {
                        $imageFile->storeAs(
                            "products/{$product->id}",
                            $productImage->filename,
                            'public'
                        );
                    }
                    // If image doesn't exist
                    else {
                        $path = $imageFile->store(
                            "products/{$product->id}",
                            'public'
                        );

                        $image_paths[] = $path;

                        $product->images()->create([
                            'filename' => basename($path),
                            'order' => $order,
                        ]);
                    }
                }
            });

            // Success
            return back()->with(
                'toast',
                [
                    'type' => 'success',
                    'message' => 'Product edited successfully'
                ]
            );
        } catch (\Throwable $e) {
            report($e);

            foreach ($image_paths as $path) {
                Storage::disk('public')->delete($path);
            }

            if (app()->environment(['local', 'development'])) {
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
        $product_id = $product->id;

        try {
            // Delete product data
            DB::transaction(function () use ($product) {
                $product->delete();
            });

            // Delete directory
            Storage::disk('public')->deleteDirectory(
                "products/{$product_id}"
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
