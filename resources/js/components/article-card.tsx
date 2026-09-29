import { Link as InertiaLink } from "@inertiajs/react";
import { ArrowRight, Calendar } from "lucide-react";
import { Article } from "@/types/models";
import { formatDate } from "@/lib/utils";

interface ArticleCardProps {
    article: Article;
}

export default function ArticleCard({ article }: ArticleCardProps) {
    return (
        <InertiaLink
            href={`/blog/${article.slug}`}
            className="group bg-muted relative block min-h-64 overflow-hidden rounded-lg"
        >
            <img
                src={`/storage/articles/featured/${article.featured_image}`}
                alt={article.title}
                loading="lazy"
                className="absolute inset-0 h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
            />

            <div
                aria-hidden
                className="absolute inset-0 bg-gradient-to-t from-black/85 via-black/45 to-transparent"
            />

            <div className="absolute inset-x-0 bottom-0 flex flex-col items-start gap-2 p-5">
                <div className="flex items-center gap-2 text-sm text-white/80">
                    <Calendar size={12} />
                    {formatDate(article.published_at ?? article.created_at)}
                </div>

                <h2 className="line-clamp-2 text-lg font-bold text-white">
                    {article.title}
                </h2>

                <span className="group-hover:text-primary flex items-center gap-1 font-semibold text-white transition-colors duration-500 ease-out">
                    Read More
                    <ArrowRight
                        size={16}
                        className="transition-transform duration-500 ease-out group-hover:translate-x-1"
                    />
                </span>
            </div>
        </InertiaLink>
    );
}
