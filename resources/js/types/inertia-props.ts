import type { PageProps } from "@inertiajs/core";
import { Article, User } from "@/types/models";

// Shared: flash
export interface FlashProps extends PageProps {
    flash: {
        toast: { type: "normal" | "success" | "error"; message: string };
        formError: string;
    };
}

// Shared: auth
export interface AuthProps extends PageProps {
    auth: {
        user: User | null;
    };
}

// Shared: all
export interface InertiaSharedProps extends FlashProps, AuthProps {}

// Data: pagination
export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedProps<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

// Data: article
export interface ArticleProps extends Article, PageProps {}
