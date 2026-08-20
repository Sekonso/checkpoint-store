import type { PageProps } from "@inertiajs/core";

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: "admin" | "customer";
}

export interface AppPageProps extends PageProps {
    flash: {
        error: string | null;
        success: string | null;
    };

    auth: {
        user: AuthUser | null;
    };
}
