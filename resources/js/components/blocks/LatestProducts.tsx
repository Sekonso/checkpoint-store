import { Link as InertiaLink } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import HomeProductCard from "@/components/home-product-card";
import { Product } from "@/types/models";

interface LatestProductsProps {
    products: Product[];
}

export default function LatestProducts({ products }: LatestProductsProps) {
    if (!products.length) {
        return null;
    }

    return (
        <section id="latest-products" className="wrapper scroll-mt-24 py-16">
            <div className="mb-10 flex flex-col items-center gap-3 text-center">
                <p className="text-primary text-sm font-medium tracking-wider uppercase">
                    Fresh Drop
                </p>
                <h2 className="text-3xl font-bold md:text-4xl">
                    Latest Products
                </h2>
                <p className="text-muted-foreground max-w-xl">
                    The newest gear to land in the store. Grab them before they
                    are gone.
                </p>
            </div>

            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {products.map((product) => (
                    <HomeProductCard key={product.id} product={product} />
                ))}
            </div>

            <div className="flex justify-center mt-6">
                <Button
                    variant="outline"
                    size="lg"
                    render={<InertiaLink href="/store" />}
                    className="px-6 py-4"
                >
                    View all products
                </Button>
            </div>
        </section>
    );
}
