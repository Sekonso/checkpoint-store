import { ProductsTable } from "@/components/data-table/product-table";
import { FilterBar, FilterOption } from "@/components/filter-bar";
import { Pagination } from "@/components/pagination";
import { SearchBar } from "@/components/search-bar";
import AdminLayout from "@/layouts/AdminLayout";
import { PaginatedProps } from "@/types/inertia-props";
import { Product } from "@/types/models";
import { PageProps } from "@inertiajs/core";
import { Head, usePage } from "@inertiajs/react";

interface AdminProductsInertiaProps extends PageProps {
    querySearch: string;
    queryDisplay: string;
    paginatedProducts: PaginatedProps<Product>;
}

export default function AdminProductsPage() {
    const { querySearch, queryDisplay, paginatedProducts } =
        usePage<AdminProductsInertiaProps>().props;

    const displayOptions: FilterOption[] = [
        { value: "", label: "all" },
        { value: "true", label: "displayed" },
        { value: "false", label: "non-displayed" },
    ];

    return (
        <>
            <Head title="New article form" />

            <AdminLayout>
                <header className="mb-10">
                    <h1 className="text-center text-2xl font-bold sm:text-3xl">
                        All Products
                    </h1>
                </header>

                {/* Toolbar */}
                <div className="mb-4 flex items-center justify-end gap-4">
                    <div className="w-full">
                        <SearchBar
                            param="search"
                            url="/admin/products"
                            initialValue={querySearch}
                            size="small"
                        />
                    </div>
                    <FilterBar
                        param="display"
                        url="/admin/products"
                        initialValue={queryDisplay}
                        options={displayOptions}
                    />
                </div>

                {/* Content */}
                <div>
                    {paginatedProducts.data.length > 0 ? (
                        <>
                            <ProductsTable products={paginatedProducts.data} />
                            <div className="my-4"></div>
                            <Pagination {...paginatedProducts} />
                        </>
                    ) : (
                        <div className="font-heading py-12 text-center text-xl font-semibold">
                            <span>There is no product here...</span>
                        </div>
                    )}
                </div>
            </AdminLayout>
        </>
    );
}
