import * as React from "react";

import { NavSection } from "@/components/nav-section";
import { NavMain } from "@/components/nav-main";
import { NavUser } from "@/components/nav-user";
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from "@/components/ui/sidebar";
import {
    LayoutDashboardIcon,
    DatabaseIcon,
    GamepadDirectional,
    Plus,
    Newspaper,
} from "lucide-react";
import { usePage } from "@inertiajs/react";
import { AppPageProps } from "@/types/SessionTypes";

export function AppSidebar({ ...props }: React.ComponentProps<typeof Sidebar>) {
    const { props: inertiaProps } = usePage<AppPageProps>();
    const { user: authUser } = inertiaProps.auth;

    const data = {
        user: {
            name: authUser?.name,
            email: authUser?.email,
        },
        navMain: [
            {
                title: "Dashboard",
                url: "#",
                icon: <LayoutDashboardIcon />,
            },
        ],
        navSections: {
            Articles: [
                {
                    name: "All Articles",
                    url: "#",
                    icon: <Newspaper />,
                },
                {
                    name: "Create Article",
                    url: "#",
                    icon: <Plus />,
                },
            ],
            Products: [
                {
                    name: "All Products",
                    url: "#",
                    icon: <DatabaseIcon />,
                },
                {
                    name: "Create Products",
                    url: "#",
                    icon: <Plus />,
                },
            ],
        },
    };

    return (
        <Sidebar collapsible="offcanvas" {...props}>
            {/* Header */}
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            className="data-[slot=sidebar-menu-button]:p-1.5!"
                            render={<a href="#" />}
                        >
                            <GamepadDirectional size={24} />
                            <span className="font-heading text-lg font-bold">
                                Checkpoint Store
                            </span>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            {/* Menu Content */}
            <SidebarContent>
                <NavMain items={data.navMain} />
                {Object.entries(data.navSections).map(([title, items]) => (
                    <NavSection key={title} title={title} items={items} />
                ))}
            </SidebarContent>

            {/* Footer */}
            <SidebarFooter>
                <NavUser user={data.user} />
            </SidebarFooter>
        </Sidebar>
    );
}
