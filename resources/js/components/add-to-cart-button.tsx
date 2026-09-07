import { AuthProps, CartProps } from "@/types/inertia-props";
import { usePage } from "@inertiajs/react";
import { Link as InertiaLink, useForm } from "@inertiajs/react";
import { Button, buttonVariants } from "@/components/ui/button";
import { Plus } from "lucide-react";
import { toast } from "sonner";

export default function AddToCartButton({
    product_id,
}: {
    product_id: number;
}) {
    const { auth, cart } = usePage<AuthProps & CartProps>().props;
    const { post } = useForm({
        product_id: product_id,
    });

    function isProductInCart(productId: number): boolean {
        return Boolean(
            cart?.items.find((item) => item.product_id === productId),
        );
    }

    function addCartHandler() {
        const toastId = "add-to-cart";
        const toastPosition = { position: "top-right" as const };

        post(`/cart`, {
            onStart: () => {
                toast.loading("Adding to the cart...", {
                    id: toastId,
                    ...toastPosition,
                });
            },
            onSuccess: () => {
                toast.success("Successfully added the product to cart", {
                    id: toastId,
                    ...toastPosition,
                });
            },
            onError: () => {
                toast.error("Failed to add the product to cart.", {
                    id: toastId,
                    ...toastPosition,
                });
            },
            preserveScroll: true,
        });
    }

    return auth.user ? (
        // Authenticated user
        isProductInCart(product_id) ? (
            <Button disabled>
                Telah ditambahkan
            </Button>
        ) : (
            <Button onClick={addCartHandler}>
                <Plus />
                Tambah ke Keranjang
            </Button>
        )
    ) : (
        // Guest user
        <InertiaLink href="/sign-in">
            <Button className="w-full">
                <Plus />
                Tambah ke Keranjang
            </Button>
        </InertiaLink>
    );
}
