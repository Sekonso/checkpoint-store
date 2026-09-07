import { formatCurrency } from "@/lib/utils";
import { router, useForm } from "@inertiajs/react";
import { Minus, Plus, ShoppingBag, Trash2 } from "lucide-react";
import { useEffect, useState } from "react";
import { Button } from "./ui/button";
import { CartItem, Product } from "@/types/models";

export default function CartItemRow({ item }: { item: CartItem }) {
    const [quantity, setQuantity] = useState(item.quantity);
    const { delete: destroy, processing } = useForm();
    const [updating, setUpdating] = useState(false);
    const product: Product = item.product;

    useEffect(() => {
        setQuantity(item.quantity);
    }, [item.quantity]);

    if (!product) {
        return null;
    }

    function updateQuantity(quantity: number) {
        if (
            quantity < 1 ||
            quantity > product.stock ||
            updating ||
            processing
        ) {
            return;
        }

        setQuantity(quantity);
        setUpdating(true);

        router.patch(
            `/cart/items/${item.id}`,
            { quantity: quantity },
            {
                preserveScroll: true,
                onFinish: () => setUpdating(false),
            },
        );
    }

    function removeItem() {
        destroy(`/cart/items/${item.id}`, {
            preserveScroll: true,
        });
    }

    const image = product.images?.[0];

    return (
        <div className="flex flex-col gap-4 border-b py-5 last:border-b-0 sm:flex-row sm:items-center">
            {/* Product image */}
            <div className="bg-muted flex h-32 w-full shrink-0 items-center justify-center overflow-hidden rounded-lg sm:h-24 sm:w-28">
                {image ? (
                    <img
                        src={`/storage/products/${product.id}/${image.filename}`}
                        alt={product.name}
                        className="h-full w-full object-cover"
                    />
                ) : (
                    <ShoppingBag
                        className="text-muted-foreground"
                        aria-hidden="true"
                    />
                )}
            </div>

            {/* Product details */}
            <div className="min-w-0 flex-1">
                <h2 className="mb-2 truncate text-lg font-semibold capitalize">
                    {product.name}
                </h2>
                <p className="text-muted-foreground text-xs">
                    {formatCurrency(product.price)} x {quantity}
                </p>
                <p className="font-semibold">
                    {formatCurrency(product.price * quantity)}
                </p>
            </div>

            {/* Items options */}
            <div className="flex items-center justify-between gap-4 sm:flex-col sm:items-end">
                {/* Quantity controls */}
                <div className="flex items-center gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        size="icon-sm"
                        onClick={() => updateQuantity(quantity - 1)}
                        disabled={updating || processing || quantity <= 1}
                        aria-label={`Decrease ${product.name} quantity`}
                    >
                        <Minus />
                    </Button>

                    <span className="w-8 text-center font-semibold">
                        {quantity}
                    </span>

                    <Button
                        type="button"
                        variant="outline"
                        size="icon-sm"
                        onClick={() => updateQuantity(quantity + 1)}
                        disabled={
                            updating || processing || quantity >= product.stock
                        }
                        aria-label={`Increase ${product.name} quantity`}
                    >
                        <Plus />
                    </Button>
                </div>

                {/* Remove */}
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    className="text-destructive hover:text-destructive"
                    onClick={removeItem}
                    disabled={updating || processing}
                >
                    <Trash2 />
                    Remove
                </Button>
            </div>
        </div>
    );
}
