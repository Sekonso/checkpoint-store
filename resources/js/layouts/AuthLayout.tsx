import { Button } from "@/components/ui/button";
import { Link as InertiaLink } from "@inertiajs/react";
import { Home } from "lucide-react";

type MainLayoutProps = {
    children: React.ReactNode;
};

export default function MainLayout({ children }: MainLayoutProps) {
    return (
        <main className="wrapper flex h-dvh flex-col items-center justify-center gap-8">
            {children}
            <InertiaLink href="/">
                <Button className="bg-secondary px-4 py-5">
                    <Home />
                    Home
                </Button>
            </InertiaLink>
        </main>
    );
}
