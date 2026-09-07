export interface User {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    address: string | null;
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
    images: ProductImage[];
    category: ProductCategory | null;
}

export interface ProductImage {
    id: number;
    product_id: number;
    filename: string;
    order: number;
}

export interface ProductCategory {
    id: number;
    name: string;
}

export interface Cart {
    id: number;
    user_id: number;
    items: CartItem[];
}

export interface CartItem {
    id: number;
    cart_id: number;
    product_id: number;
    quantity: number;
    product: Product;
}
