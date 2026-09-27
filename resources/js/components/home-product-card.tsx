import { Link as InertiaLink } from "@inertiajs/react";
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

export default function HomeProductCard({ product }: { product: Product }) {
    const image = product.images?.[0];

    return (
        <Card className="p-0 transition-transform duration-300 hover:-translate-y-1">
            <CardHeader className="relative p-0">
                <div className="bg-muted h-50 overflow-hidden">
                    {image ? (
                        <img
                            src={`/storage/products/${product.id}/${image.filename}`}
                            alt={product.name}
                            loading="lazy"
                            className="h-full w-full object-cover transition-transform duration-300 group-hover/card:scale-105"
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

            <CardContent className="flex flex-1 flex-col px-4">
                <h3 className="line-clamp-1 text-lg font-bold">
                    {product.name}
                </h3>
                <span className="font-heading mt-2 text-lg font-bold">
                    {formatCurrency(product.price)}
                </span>
            </CardContent>

            <CardFooter>
                <InertiaLink
                    href={`/store/${product.slug}`}
                    className={cn(
                        buttonVariants({ variant: "outline" }),
                        "w-full",
                    )}
                >
                    Detail
                </InertiaLink>
            </CardFooter>
        </Card>
    );
}
