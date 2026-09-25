import { Breadcrumbs } from "@/components/breadcrumbs";
import MainLayout from "@/layouts/MainLayout";
import { formatDate } from "@/lib/utils";
import { Article } from "@/types/models";
import { PageProps } from "@inertiajs/core";
import { Head, usePage } from "@inertiajs/react";
import { Calendar, CircleUser } from "lucide-react";
import { Badge } from "@/components/ui/badge";

interface BlogDetailPageProps extends PageProps {
    article: Article;
}

export default function BlogDetailPage() {
    const { article } = usePage<BlogDetailPageProps>().props;

    const breadcrumb = [
        { name: "Home", href: "/" },
        { name: "Blog", href: "/blog" },
        { name: article.title, href: "#" },
    ];

    return (
        <>
            <Head title={article.title} />

            <MainLayout>
                <div className="wrapper flex w-full max-w-3xl flex-col gap-8 py-6 sm:py-12">
                    <Breadcrumbs items={breadcrumb} />

                    <img
                        src={`/storage/articles/featured/${article.featured_image}`}
                        className="h-70 w-full object-cover"
                    />

                    <div className="flex w-full min-w-0 max-w-240 mx-auto flex-col gap-8">
                        {/* Head */}
                        <div className="flex flex-col gap-2">
                            <h1 className="text-2xl font-bold capitalize sm:text-3xl">
                                {article.title}
                            </h1>
                            <div className="flex gap-4">
                                {/* Author */}
                                <div className="text-muted-foreground flex items-center gap-2">
                                    <CircleUser size={12} />
                                    <span className="text-sm">Sekonds</span>
                                </div>

                                {/* Date */}
                                <div className="text-muted-foreground flex items-center gap-2">
                                    <Calendar size={12} />
                                    {article.published_at && (
                                        <span className="text-sm">
                                            {formatDate(article.published_at)}
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Content */}
                        <div
                            className="tiptap-content min-w-0"
                            dangerouslySetInnerHTML={{
                                __html: article.content,
                            }}
                        />

                        {/* Tags */}
                        <ul className="flex gap-2">
                            {article.tags.map((tag) => (
                                <li key={tag.id}>
                                    <Badge className="bg-primary text-primary-foreground p-3">
                                        {tag.name}
                                    </Badge>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </MainLayout>
        </>
    );
}
