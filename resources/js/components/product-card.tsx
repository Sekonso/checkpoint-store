import { Link as InertiaLink, usePage } from "@inertiajs/react";
import { Badge } from "@/components/ui/badge";
import { buttonVariants } from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
} from "@/components/ui/card";
import { Product } from "@/types/models";
import { cn, formatCurrency } from "@/lib/utils";
import AddToCartButton from "./add-to-cart-button";

export default function ProductCard({ product }: { product: Product }) {
    const image = product.images?.[0];

    return (
        <Card className="overflow-hidden p-0">
            {/* Header */}
            <CardHeader className="relative p-0">
                <div className="bg-muted h-50 overflow-hidden">
                    {image ? (
                        <img
                            src={`/storage/products/${product.id}/${image.filename}`}
                            alt={product.name}
                            className="h-full w-full object-cover"
                        />
                    ) : (
                        <div className="text-muted-foreground flex h-full items-center justify-center">
                            No image
                        </div>
                    )}
                </div>
                {product.category && (
                    <Badge className="absolute top-3 right-3">
                        {product.category.name}
                    </Badge>
                )}
            </CardHeader>

            {/* Content */}
            <CardContent className="flex flex-col px-4">
                <h2 className="line-clamp-1 text-lg font-bold">
                    {product.name}
                </h2>
                <span className="text-muted-foreground text-sm">
                    {product.stock > 0
                        ? `Stock: ${product.stock}`
                        : "Out of stock"}
                </span>
                <span className="font-heading mt-2 text-lg font-bold">
                    {formatCurrency(product.price)}
                </span>
            </CardContent>

            {/* Footer */}
            <CardFooter className="flex items-center justify-center gap-4">
                <InertiaLink
                    href={`/store/${product.slug}`}
                    className={cn(
                        buttonVariants({ variant: "outline" }),
                        "flex-1",
                    )}
                >
                    Detail
                </InertiaLink>

                <AddToCartButton product_id={product.id} />
            </CardFooter>
        </Card>
    );
}
