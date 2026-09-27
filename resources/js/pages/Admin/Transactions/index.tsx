import { TransactionsTable } from "@/components/data-table/transactions-table";
import { FilterBar, FilterOption } from "@/components/filter-bar";
import { Pagination } from "@/components/pagination";
import { SearchBar } from "@/components/search-bar";
import AdminLayout from "@/layouts/AdminLayout";
import { PaginatedProps } from "@/types/inertia-props";
import { Transaction } from "@/types/models";
import { PageProps } from "@inertiajs/core";
import { Head, usePage } from "@inertiajs/react";

interface AdminTransactionsInertiaProps extends PageProps {
    querySearch: string;
    queryStatus: string;
    paginatedTransactions: PaginatedProps<Transaction>;
}

export default function AdminTransactionsPage() {
    const { querySearch, queryStatus, paginatedTransactions } =
        usePage<AdminTransactionsInertiaProps>().props;

    const statusOptions: FilterOption[] = [
        { value: "all", label: "all" },
        { value: "pending", label: "pending" },
        { value: "paid", label: "paid" },
        { value: "cancelled", label: "cancelled" },
    ];

    return (
        <>
            <Head title="All Transactions" />

            <AdminLayout>
                <header className="mb-10">
                    <h1 className="text-center text-2xl font-bold sm:text-3xl">
                        All Transactions
                    </h1>
                </header>

                {/* Toolbar */}
                <div className="mb-4 flex items-center justify-end gap-4">
                    <div className="w-full">
                        <SearchBar
                            param="search"
                            url="/admin/transactions"
                            initialValue={querySearch}
                            placeholder="Search invoice number..."
                            inputClassName="h-10"
                        />
                    </div>
                    <FilterBar
                        param="status"
                        url="/admin/transactions"
                        initialValue={queryStatus}
                        options={statusOptions}
                        selectClassName="h-10"
                    />
                </div>

                {/* Content */}
                <div>
                    {paginatedTransactions.data.length > 0 ? (
                        <>
                            <TransactionsTable
                                transactions={paginatedTransactions.data}
                            />
                            <div className="my-4"></div>
                            <Pagination {...paginatedTransactions} />
                        </>
                    ) : (
                        <div className="font-heading py-12 text-center text-xl font-semibold">
                            <span>There is no transaction here...</span>
                        </div>
                    )}
                </div>
            </AdminLayout>
        </>
    );
}
