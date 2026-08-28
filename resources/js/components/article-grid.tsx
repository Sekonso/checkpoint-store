import type { Article } from "@/types/models";
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
} from "@/components/ui/card";
import { Calendar } from "lucide-react";
import { Link as InertiaLink } from "@inertiajs/react";
import { formatDate } from "@/lib/utils";

interface ArticleGridProps {
    articles: Article[];
}

export default function ArticleGrid({ articles }: ArticleGridProps) {
    return (
        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
            {articles.map((item) => (
                <InertiaLink
                    key={item.id}
                    href={`blog/${item.slug}`}
                    className="group shadow-sm hover:-translate-y-1 hover:shadow-lg"
                >
                    <Card className="p-0">
                        <CardHeader className="p-0">
                            <div className="overflow-hidden">
                                <img
                                    src={`/storage/articles/featured/${item.featured_image}`}
                                    alt="Article image preview"
                                    className="h-50 w-full object-cover"
                                />
                            </div>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-2">
                            <div className="text-muted-foreground flex items-center gap-2">
                                <Calendar size={12} />
                                <span className="text-sm">
                                    {formatDate(item.created_at)}
                                </span>
                            </div>
                            <h2 className="line-clamp-2 h-14 text-lg font-bold">
                                {item.title}
                            </h2>
                        </CardContent>
                        <CardFooter className="bg-card">
                            <span className="group-hover:text-primary font-semibold">
                                Read More
                            </span>
                        </CardFooter>
                    </Card>
                </InertiaLink>
            ))}
        </div>
    );
}
