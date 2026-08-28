import ArticleGrid from "@/components/article-grid";
import { Pagination } from "@/components/pagination";
import { SearchBar } from "@/components/search-bar";
import MainLayout from "@/layouts/MainLayout";
import { PaginatedProps } from "@/types/inertia-props";
import { Article } from "@/types/models";
import { PageProps } from "@inertiajs/core";
import { Head, usePage } from "@inertiajs/react";

interface BlogPageProps extends PageProps {
    querySearch: string;
    articles: PaginatedProps<Article>;
}

export default function BlogPage() {
    const { querySearch, articles } = usePage<BlogPageProps>().props;

    console.log(querySearch);

    return (
        <>
            <Head title="News and Blog" />

            <MainLayout>
                <div className="wrapper flex flex-col gap-8">
                    {/* Head */}
                    <section className="bg-red-gradient">
                        <h1 className="font-heading py-12 text-center text-4xl font-bold tracking-wider text-white uppercase">
                            News and Blog
                        </h1>
                    </section>

                    {/* Toolbar */}
                    <div>
                        <SearchBar
                            param="search"
                            url="/blog"
                            initialValue={querySearch}
                        />
                    </div>

                    {/* Article list */}
                    <div>
                        {articles.data.length > 0 ? (
                            <>
                                <ArticleGrid articles={articles.data} />
                                <div className="my-4"></div>
                                <Pagination {...articles} />
                            </>
                        ) : (
                            <div className="font-heading py-12 text-center text-xl font-semibold">
                                <span>There is no article here...</span>
                            </div>
                        )}
                    </div>
                </div>
            </MainLayout>
        </>
    );
}
