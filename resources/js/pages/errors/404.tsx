import MainLayout from "@/layouts/MainLayout";
import { Link } from "@inertiajs/react";

export default function NotFound() {
    return (
        <MainLayout>
            <div className="wrapper flex flex-col items-center py-25">
                <div className="flex flex-col items-center gap-2">
                    <span className="font-heading text-6xl font-bold">404</span>
                    <h1 className="font-heading text-4xl font-bold">
                        Page Not Found
                    </h1>
                </div>
                <Link href="/" className="text-primary mt-4 font-semibold">
                    Back to home
                </Link>
            </div>
        </MainLayout>
    );
}
