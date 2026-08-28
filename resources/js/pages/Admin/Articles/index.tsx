import { ArticlesTable } from "@/components/articles-table";
import { FilterBar, FilterOption } from "@/components/filter-bar";
import { Pagination } from "@/components/pagination";
import { SearchBar } from "@/components/search-bar";
import AdminLayout from "@/layouts/AdminLayout";
import { PaginatedProps } from "@/types/inertia-props";
import { Article } from "@/types/models";
import { PageProps } from "@inertiajs/core";
import { Head, usePage } from "@inertiajs/react";

interface AdminArticleInertiaProps extends PageProps {
    querySearch: string;
    queryStatus: string;
    paginatedArticles: PaginatedProps<Article>;
}

export default function AdminArticlePage() {
    const { querySearch, queryStatus, paginatedArticles } =
        usePage<AdminArticleInertiaProps>().props;

    const status = ["all", "draft", "published", "archived"];
    const statusOptions: FilterOption[] = status.map((item) => ({
        value: item,
        label: item,
    }));

    return (
        <>
            <Head title="New article form" />

            <AdminLayout>
                <header className="mb-10">
                    <h1 className="text-center text-2xl font-bold sm:text-3xl">
                        All Articles
                    </h1>
                </header>

                {/* Toolbar */}
                <div className="mb-4 flex items-center justify-end gap-4">
                    <div className="w-full">
                        <SearchBar
                            param="search"
                            url="/admin/articles"
                            initialValue={querySearch}
                            size="small"
                        />
                    </div>
                    <FilterBar
                        param="status"
                        url="/admin/articles"
                        initialValue={queryStatus}
                        options={statusOptions}
                        size="small"
                    />
                </div>

                {/* Content */}
                <div>
                    {paginatedArticles.data.length > 0 ? (
                        <>
                            <ArticlesTable articles={paginatedArticles.data} />
                            <div className="my-4"></div>
                            <Pagination {...paginatedArticles} />
                        </>
                    ) : (
                        <div className="font-heading py-12 text-center text-xl font-semibold">
                            <span>There is no article here...</span>
                        </div>
                    )}
                </div>
            </AdminLayout>
        </>
    );
}
