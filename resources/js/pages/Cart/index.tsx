import MainLayout from "@/layouts/MainLayout";
import { Cart } from "@/types/models";
import { formatCurrency } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
    Head,
    Link as InertiaLink,
    Form as InertiaForm,
    useForm,
    usePage,
} from "@inertiajs/react";
import { ShoppingBag, Trash2 } from "lucide-react";
import { PageProps } from "@inertiajs/core";
import CartItemRow from "@/components/cart-item-row";
import { FormError } from "@/components/form/form-error";
import { FlashProps } from "@/types/inertia-props";

interface CartPageProps extends PageProps, FlashProps {
    cart: Cart;
}

export default function CartPage() {
    const { cart, flash } = usePage<CartPageProps>().props;
    const { delete: clearCart, processing } = useForm();

    const totalQuantity = cart.items.reduce(
        (total, item) => total + item.quantity,
        0,
    );

    const totalPrice = cart.items.reduce(
        (total, item) => total + (item.product?.price ?? 0) * item.quantity,
        0,
    );

    function handleClearCart() {
        clearCart("/cart", { preserveScroll: true });
    }

    return (
        <>
            <Head title="My Cart" />

            <MainLayout>
                <div className="wrapper py-10">
                    {/* Header bar */}
                    <div className="mb-8 flex items-center justify-between gap-4">
                        <div>
                            <h1 className="font-heading text-3xl font-bold">
                                My Cart
                            </h1>

                            <p className="text-muted-foreground mt-1">
                                {totalQuantity}{" "}
                                {totalQuantity === 1 ? "item" : "items"}
                            </p>
                        </div>

                        {/* Clear cart button */}
                        {cart.items.length > 0 && (
                            <Button
                                variant="outline"
                                onClick={handleClearCart}
                                disabled={processing}
                            >
                                <Trash2 />
                                Clear cart
                            </Button>
                        )}
                    </div>

                    {cart.items.length === 0 ? (
                        // Cart empty
                        <Card>
                            <CardContent className="flex flex-col items-center gap-4 py-16 text-center">
                                <ShoppingBag className="text-muted-foreground size-12" />

                                <div>
                                    <h2 className="font-heading text-xl font-semibold">
                                        Your cart is empty
                                    </h2>
                                    <p className="text-muted-foreground mt-1">
                                        Discover your gear.
                                    </p>
                                </div>

                                <InertiaLink href="/store">
                                    <Button>Go to store</Button>
                                </InertiaLink>
                            </CardContent>
                        </Card>
                    ) : (
                        <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
                            {/* Cart items list */}
                            <Card>
                                <CardContent className="pt-2">
                                    {cart.items.map((item) => (
                                        <CartItemRow
                                            key={item.id}
                                            item={item}
                                        />
                                    ))}
                                </CardContent>
                            </Card>

                            {/* Summary and checkout */}
                            <div>
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Summary</CardTitle>
                                    </CardHeader>

                                    <CardContent className="flex justify-between border-t pt-4 text-lg font-bold">
                                        <span>Total</span>
                                        <span>
                                            {formatCurrency(totalPrice)}
                                        </span>
                                    </CardContent>
                                </Card>
                                <InertiaForm
                                    action="/transactions"
                                    method="post"
                                >
                                    {({ processing: isCheckingOut }) => (
                                        <>
                                            <Button
                                                type="submit"
                                                className="font-heading mt-4 w-full py-2 font-semibold"
                                                disabled={isCheckingOut}
                                            >
                                                {isCheckingOut
                                                    ? "Processing..."
                                                    : "Checkout"}
                                            </Button>

                                            {flash?.formError && (
                                                <FormError
                                                    title="Checkout failed"
                                                    message={flash.formError}
                                                />
                                            )}
                                        </>
                                    )}
                                </InertiaForm>
                            </div>
                        </div>
                    )}
                </div>
            </MainLayout>
        </>
    );
}
