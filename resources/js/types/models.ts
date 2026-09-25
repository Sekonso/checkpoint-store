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

export interface Transaction {
    id: number;
    user_id: number;
    invoice_number: string;
    name: string;
    phone: string;
    address: string;
    total_amount: number;
    status: "pending" | "paid" | "cancelled";
    payment_type: string | null;
    paid_at: string | null;
    expires_at: string | null;
    is_complete: boolean;
    created_at: string;
    updated_at: string;
    items: TransactionItem[];
    user?: User;
}

export interface Payment {
    id: number;
    transaction_id: number;
    status: "active" | "paid" | "expired" | "cancelled";
    method: string | null;
    amount: number;
    paid_at: string | null;
    expires_at: string | null;
}

export interface CompletedPayment {
    method: string | null;
    amount: number;
    paid_at: string | null;
    status: string;
}

export interface TransactionItem {
    id: number;
    transaction_id: number;
    product_id: number | null;
    product_name: string;
    price: number;
    quantity: number;
    subtotal: number;
    created_at: string;
    updated_at: string;
}
