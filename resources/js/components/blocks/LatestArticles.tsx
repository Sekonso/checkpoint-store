import { Link as InertiaLink } from "@inertiajs/react";
import ArticleGrid from "@/components/article-grid";
import { Button } from "@/components/ui/button";
import { Article } from "@/types/models";

interface LatestArticlesProps {
    articles: Article[];
}

export default function LatestArticles({ articles }: LatestArticlesProps) {
    if (!articles.length) {
        return null;
    }

    return (
        <section id="latest-articles" className="wrapper scroll-mt-24 py-16">
            <div className="mb-10 flex flex-col items-center gap-3 text-center">
                <p className="text-primary text-sm font-medium tracking-wider uppercase">
                    From the Blog
                </p>
                <h2 className="text-3xl font-bold md:text-4xl">
                    Latest Articles
                </h2>
                <p className="text-muted-foreground max-w-xl">
                    Guides, news and stories from the Checkpoint team.
                </p>
            </div>

            <ArticleGrid articles={articles} columns={3} />

                        <div className="flex justify-center mt-6">
                <Button
                    variant="outline"
                    size="lg"
                    render={<InertiaLink href="/store" />}
                    className="px-6 py-4"
                >
                    Check our latest news
                </Button>
            </div>
        </section>
    );
}
