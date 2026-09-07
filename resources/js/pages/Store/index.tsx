import ProductCard from "@/components/product-card";
import { Pagination } from "@/components/pagination";
import { SearchBar } from "@/components/search-bar";
import { FilterBar, FilterOption } from "@/components/filter-bar";
import MainLayout from "@/layouts/MainLayout";
import { PaginatedProps } from "@/types/inertia-props";
import { Product, ProductCategory } from "@/types/models";
import { Head, usePage } from "@inertiajs/react";
import { PageProps } from "@inertiajs/core";

interface StorePageProps extends PageProps {
    querySearch: string | null;
    queryCategory: string | null;
    products: PaginatedProps<Product>;
    categories: ProductCategory[];
}

export default function StorePage() {
    const { querySearch, queryCategory, products, categories } =
        usePage<StorePageProps>().props;
    const categoryOptions: FilterOption[] = [
        { value: "", label: "All categories" },
        ...categories.map((category) => ({
            value: String(category.id),
            label: category.name,
        })),
    ];

    return (
        <>
            <Head title="Store" />

            <MainLayout>
                <div className="wrapper flex flex-col gap-8">
                    <section className="bg-red-gradient">
                        <h1 className="font-heading py-12 text-center text-4xl font-bold tracking-wider text-white uppercase">
                            Store
                        </h1>
                    </section>

                    {/* Toolbar */}
                    <div className="flex flex-col items-center gap-4 sm:flex-row">
                        <div className="flex-1">
                            <SearchBar
                                param="search"
                                url="/store"
                                initialValue={querySearch ?? ""}
                                className="h-12"
                                inputClassName="h-full py-0"
                                buttonClassName="h-full py-0"
                            />
                        </div>

                        <FilterBar
                            param="category"
                            url="/store"
                            initialValue={queryCategory ?? ""}
                            options={categoryOptions}
                            className="h-12"
                            selectClassName="h-full"
                        />
                    </div>

                    {/* Product Grid */}
                    {products.data.length ? (
                        <>
                            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                {products.data.map((product) => (
                                    <ProductCard
                                        key={product.id}
                                        product={product}
                                    />
                                ))}
                            </div>
                            <div className="my-4" />
                            <Pagination {...products} />
                        </>
                    ) : (
                        <div className="font-heading py-12 text-center text-xl font-semibold">
                            There is no product here...
                        </div>
                    )}
                </div>
            </MainLayout>
        </>
    );
}
