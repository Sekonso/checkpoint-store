import { SidebarProvider, SidebarTrigger } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/sidebar/app-sidebar";
import { Separator } from "@base-ui/react";
import { ToastLoader } from "@/components/toast-loader";

type AdminLayoutProps = {
    children: React.ReactNode;
};

export default function AdminLayout({ children }: AdminLayoutProps) {
    return (
        <>
            {/* Notifications */}
            <ToastLoader />

            <SidebarProvider>
                <AppSidebar />
                <main className="flex-1">
                    {/* Top section */}
                    <section className="bg-background border-muted-foreground sticky top-0 z-999 flex w-full items-center gap-4 border-b p-4">
                        <SidebarTrigger />

                        <Separator
                            orientation="vertical"
                            className="bg-muted-foreground h-6 w-px shrink-0"
                        />

                        <h1 className="font-heading text-xl font-bold">
                            Admin
                        </h1>
                    </section>

                    {/* Content */}
                    <div className="px-4 py-8">{children}</div>
                </main>
            </SidebarProvider>
        </>
    );
}
