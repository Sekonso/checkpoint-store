import { Link as InertiaLink } from "@inertiajs/react";
import HomeProductCard from "@/components/home-product-card";
import { Product } from "@/types/models";
import { ArrowRight } from "lucide-react";

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
                    Take a look at
                </p>
                <h2 className="font-heading text-3xl font-bold md:text-4xl">
                    Our Latest Products
                </h2>
            </div>

            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {products.map((product) => (
                    <HomeProductCard key={product.id} product={product} />
                ))}
            </div>

            <div className="mt-8 flex flex-col items-end gap-4">
                <div className="w-60 h-1 bg-primary"></div>
                <InertiaLink
                    href="/store"
                    className="group text-xl font-heading text-foreground hover:text-primary inline-flex items-center gap-1 font-semibold transition-colors"
                >
                    GO TO STORE
                    <ArrowRight className="size-4 transition-transform duration-300 group-hover:translate-x-1" />
                </InertiaLink>
            </div>
        </section>
    );
}
