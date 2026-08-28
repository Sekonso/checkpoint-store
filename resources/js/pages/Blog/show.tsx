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

export default function BlogDeailPage() {
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
                <div className="wrapper flex w-250 flex-col gap-8 py-6 sm:py-12">
                    <Breadcrumbs items={breadcrumb} />

                    <img
                        src={`/storage/articles/featured/${article.featured_image}`}
                        className="h-70 w-full object-cover"
                    />

                    <div className="mx-auto flex w-250 flex-col gap-8">
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
                                    <span className="text-sm">
                                        {formatDate(article.created_at)}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* Content */}
                        <div
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
