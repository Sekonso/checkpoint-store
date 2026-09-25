<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class ProductService
{
    public function paginateForStore(?string $search, ?string $categoryId): LengthAwarePaginator
    {
        return Product::query()
            ->with(['images', 'category'])
            ->where('in_display', true)
            ->where('stock', '>', 0)
            ->when(
                $search,
                fn (Builder $query, string $search) => $query->where('name', 'like', "%{$search}%")
            )
            ->when(
                $categoryId,
                fn (Builder $query, string $categoryId) => $query->where('category_id', $categoryId)
            )
            ->latest()
            ->paginate(12)
            ->withQueryString();
    }

    public function getDisplayableProduct(Product $product): ?Product
    {
        if (! $product->in_display) {
            return null;
        }

        return $product->load(['images', 'category']);
    }

    public function categories(): Collection
    {
        return ProductCategory::query()->orderBy('name')->get();
    }

    public function paginateForAdmin(?string $search, ?string $display): LengthAwarePaginator
    {
        return Product::query()
            ->when(
                $search,
                fn (Builder $query, string $search) => $query->whereLike('name', "%{$search}%", caseSensitive: false)
            )
            ->when(
                \in_array($display, ['true', 'false']),
                fn (Builder $query) => $query->where('in_display', filter_var($display, FILTER_VALIDATE_BOOLEAN))
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function create(array $data, array $images): Product
    {
        $storedPaths = [];

        try {
            return DB::transaction(function () use ($data, $images, &$storedPaths) {
                $product = Product::create($data);

                foreach ($images as $order => $image) {
                    if (! $image instanceof UploadedFile) {
                        continue;
                    }

                    $path = $image->store("products/{$product->id}", 'public');

                    $storedPaths[] = $path;

                    $product->images()->create([
                        'filename' => basename($path),
                        'order' => $order,
                    ]);
                }

                return $product;
            });
        } catch (Throwable $e) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }
    }

    public function update(Product $product, array $data, array $images): void
    {
        $storedPaths = [];

        try {
            DB::transaction(function () use ($product, $data, $images, &$storedPaths) {
                $product->update($data);

                foreach ($images as $order => $image) {
                    if (! $image instanceof UploadedFile) {
                        continue;
                    }

                    $productImage = $product->images()
                        ->where('order', $order)
                        ->first();

                    if ($productImage) {
                        $image->storeAs(
                            "products/{$product->id}",
                            $productImage->filename,
                            'public'
                        );
                    } else {
                        $path = $image->store(
                            "products/{$product->id}",
                            'public'
                        );

                        $storedPaths[] = $path;

                        $product->images()->create([
                            'filename' => basename($path),
                            'order' => $order,
                        ]);
                    }
                }
            });
        } catch (Throwable $e) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }
    }

    public function delete(Product $product): void
    {
        $productId = $product->id;

        DB::transaction(fn () => $product->delete());

        Storage::disk('public')->deleteDirectory("products/{$productId}");
    }
}
