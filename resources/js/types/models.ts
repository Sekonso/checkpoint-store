export interface User {
    id: number;
    name: string;
    email: string;
    role: "admin" | "customer";
}

export interface Article {
    id: number;
    user: User;
    title: string;
    slug: string;
    content: string;
    featured_image: string;
    tags: ArticleTag[];
    status: "draft" | "published" | "archived";
    created_at: string;
}

export interface ArticleTag {
    id: number;
    name: string;
}
