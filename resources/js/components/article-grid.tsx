import type { Article } from "@/types/models";
import ArticleCard from "@/components/article-card";
import { cn } from "@/lib/utils";

interface ArticleGridProps {
    articles: Article[];
    columns?: 2 | 3;
}

export default function ArticleGrid({
    articles,
    columns = 2,
}: ArticleGridProps) {
    return (
        <div
            className={cn(
                "grid grid-cols-1 gap-6 sm:grid-cols-2",
                columns === 3 && "lg:grid-cols-3",
            )}
        >
            {articles.map((item) => (
                <ArticleCard key={item.id} article={item} />
            ))}
        </div>
    );
}
