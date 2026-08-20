import { SidebarProvider, SidebarTrigger } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/app-sidebar";
import { Separator } from "@base-ui/react";

type AdminLayoutProps = {
    children: React.ReactNode;
};

export default function AdminLayout({ children }: AdminLayoutProps) {
    return (
        <SidebarProvider>
            <AppSidebar />
            <main className="flex-1">
                {/* Header */}
                <header className="border-muted-foreground flex w-full items-center gap-4 border-b p-4">
                    <SidebarTrigger />

                    <Separator
                        orientation="vertical"
                        className="bg-muted-foreground h-6 w-px shrink-0"
                    />

                    <h1 className="font-heading text-xl font-bold">Admin</h1>
                </header>

                {/* Content */}
                <div className="p-4">{children}</div>
            </main>
        </SidebarProvider>
    );
}
