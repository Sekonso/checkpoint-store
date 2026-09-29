import { Link as InertiaLink } from "@inertiajs/react";
import { ArrowRight } from "lucide-react";
import { Article } from "@/types/models";
import { cn, formatDate } from "@/lib/utils";

interface SimpleArticleCardProps {
    article: Article;
    size?: "large" | "small";
}

export default function SimpleArticleCard({
    article,
    size = "small",
}: SimpleArticleCardProps) {
    const isLarge = size === "large";

    return (
        <InertiaLink
            href={`/blog/${article.slug}`}
            className={cn(
                "group bg-muted relative block overflow-hidden rounded-xl",
                isLarge ? "min-h-[26rem]" : "min-h-[9rem]",
            )}
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

            <div
                className={cn(
                    "absolute inset-x-0 bottom-0 flex flex-col items-start gap-2",
                    isLarge ? "p-6 md:p-8" : "p-4",
                )}
            >
                <div className="text-sm text-white/80">
                    {formatDate(article.published_at ?? article.created_at)}
                </div>

                <h3
                    className={cn(
                        "font-bold text-white",
                        isLarge
                            ? "line-clamp-2 text-2xl md:text-3xl"
                            : "line-clamp-1 text-base md:text-lg",
                    )}
                >
                    {article.title}
                </h3>

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
