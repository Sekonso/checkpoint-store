import { Link as InertiaLink } from "@inertiajs/react";
import SimpleArticleCard from "@/components/simple-article-card";
import { Button } from "@/components/ui/button";
import { Article } from "@/types/models";

interface LatestArticlesProps {
    articles: Article[];
}

export default function LatestArticles({ articles }: LatestArticlesProps) {
    if (!articles.length) {
        return null;
    }

    const [featured, ...rest] = articles;
    const secondary = rest.slice(0, 3);

    return (
        <section id="latest-articles" className="wrapper scroll-mt-24 py-16">
            <div className="mb-10 flex flex-wrap items-end justify-between gap-4">
                <div className="flex flex-col gap-3">
                    <h2 className="font-heading text-3xl font-bold md:text-4xl">
                        Latest Articles
                    </h2>
                    <p className="text-muted-foreground max-w-xl">
                        Guides, news and stories from the Checkpoint team.
                    </p>
                </div>

                <Button
                    size="lg"
                    render={<InertiaLink href="/blog" />}
                    className="px-6 py-4"
                >
                    See More Articles
                </Button>
            </div>

            <div className="grid gap-6 lg:grid-cols-[5.5fr_2.5fr]">
                <SimpleArticleCard article={featured} size="large" />

                {secondary.length > 0 && (
                    <div className="flex flex-col gap-6">
                        {secondary.map((article) => (
                            <SimpleArticleCard
                                key={article.id}
                                article={article}
                            />
                        ))}
                    </div>
                )}
            </div>
        </section>
    );
}
