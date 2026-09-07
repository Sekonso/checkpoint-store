import MainLayout from "@/layouts/MainLayout";
import { Product } from "@/types/models";
import { Head, usePage } from "@inertiajs/react";
import { PageProps } from "@inertiajs/core";
import { useMemo, useState } from "react";
import { Badge } from "@/components/ui/badge";
import { formatCurrency } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { Plus } from "lucide-react";
import { Breadcrumbs } from "@/components/breadcrumbs";
import AddToCartButton from "@/components/add-to-cart-button";

export default function StoreShow() {
    const { product } = usePage<PageProps & { product: Product }>().props;
    const images = useMemo(
        () => [...(product.images ?? [])].sort((a, b) => a.order - b.order),
        [product.images],
    );
    const [selectedImage, setSelectedImage] = useState(images[0] ?? null);
    const imageUrl = selectedImage
        ? `/storage/products/${product.id}/${selectedImage.filename}`
        : null;

    const breadcrumb = [
        { name: "Home", href: "/" },
        { name: "Store", href: "/store" },
        { name: product.name, href: "#" },
    ];

    return (
        <>
            <Head title={product.name} />

            <MainLayout>
                <div className="wrapper py-12">
                    <Breadcrumbs items={breadcrumb} />

                    <div className="py-2" />

                    <div className="grid gap-10 lg:grid-cols-2">
                        {/* Gallery */}
                        <section className="flex flex-col gap-4">
                            <div className="bg-muted aspect-square h-75 overflow-hidden rounded-lg sm:h-100">
                                {imageUrl ? (
                                    <img
                                        src={imageUrl}
                                        alt={product.name}
                                        className="h-full w-full object-cover"
                                    />
                                ) : (
                                    <div className="text-muted-foreground flex h-full items-center justify-center">
                                        No image available
                                    </div>
                                )}
                            </div>
                            {images.length > 1 && (
                                <div className="grid grid-cols-4 gap-3 sm:grid-cols-5">
                                    {images.map((image) => {
                                        const url = `/storage/products/${product.id}/${image.filename}`;
                                        const selected =
                                            selectedImage?.id === image.id;
                                        return (
                                            <button
                                                key={image.id}
                                                type="button"
                                                onClick={() =>
                                                    setSelectedImage(image)
                                                }
                                                aria-label={`View image ${image.order}`}
                                                aria-pressed={selected}
                                                className={`bg-muted aspect-square overflow-hidden rounded-md border-2 ${selected ? "border-primary" : "border-transparent opacity-70 hover:opacity-100"}`}
                                            >
                                                <img
                                                    src={url}
                                                    alt={`${product.name} thumbnail ${image.order}`}
                                                    className="h-full w-full object-cover"
                                                />
                                            </button>
                                        );
                                    })}
                                </div>
                            )}
                        </section>

                        {/* Content */}
                        <section className="flex flex-col gap-4">
                            {/* Category */}
                            {product.category && (
                                <Badge className="px-4 py-3">
                                    {product.category.name}
                                </Badge>
                            )}

                            <div>
                                {/* Name */}
                                <h1 className="font-heading text-3xl font-bold">
                                    {product.name}
                                </h1>

                                {/* Stock */}
                                <span className="text-muted-foreground text-sm">
                                    {product.stock > 0
                                        ? `Stock: ${product.stock}`
                                        : "Out of stock"}
                                </span>
                            </div>

                            {/* Description */}
                            {product.description && (
                                <p className="text-muted-foreground whitespace-pre-line">
                                    {product.description}
                                </p>
                            )}

                            <div className="mt-4 flex flex-col gap-4">
                                {/* Price */}
                                <span className="font-heading text-2xl font-bold">
                                    {formatCurrency(product.price)}
                                </span>

                                {/* Add to Cart */}
                                <AddToCartButton product_id={product.id} />
                            </div>
                        </section>
                    </div>
                </div>
            </MainLayout>
        </>
    );
}
