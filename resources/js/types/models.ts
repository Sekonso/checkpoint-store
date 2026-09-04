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
    published_at: string | null;
}

export interface ArticleTag {
    id: number;
    name: string;
}

export interface Product {
    id: number;
    category_id: number;
    name: string;
    slug: string;
    description: string;
    price: number;
    stock: number;
    in_display: boolean;
}

export interface ProductCategory {
    id: number;
    name: string;
}

