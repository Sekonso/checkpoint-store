import {
    Card,
    CardContent,
    CardDescription,
    CardTitle,
} from "@/components/ui/card";
import AdminLayout from "@/layouts/AdminLayout";
import { Head, Link as InertiaLink } from "@inertiajs/react";
import {
    ArrowRightIcon,
    DatabaseIcon,
    LucideIcon,
    NewspaperIcon,
    PlusIcon,
    ReceiptTextIcon,
} from "lucide-react";

type QuickAction = {
    title: string;
    description: string;
    href: string;
    icon: LucideIcon;
};

type QuickActionSection = {
    section: string;
    actions: QuickAction[];
};

const quickActions: QuickActionSection[] = [
    {
        section: "Articles",
        actions: [
            {
                title: "All Articles",
                description:
                    "Browse the full blog archive. Search, filter by status, then edit, archive, or delete any post.",
                href: "/admin/articles",
                icon: NewspaperIcon,
            },
            {
                title: "Create Article",
                description:
                    "Write a new blog post with tags, a publication status, a featured image, and a rich text editor.",
                href: "/admin/articles/create",
                icon: PlusIcon,
            },
        ],
    },
    {
        section: "Products",
        actions: [
            {
                title: "All Products",
                description:
                    "Manage the store catalog. Search, filter by visibility, and update stock, price, or images.",
                href: "/admin/products",
                icon: DatabaseIcon,
            },
            {
                title: "Create Product",
                description:
                    "List a new product with its price, stock, category, up to three images, and a description.",
                href: "/admin/products/create",
                icon: PlusIcon,
            },
        ],
    },
    {
        section: "Transactions",
        actions: [
            {
                title: "All Transactions",
                description:
                    "Review customer orders. Search by invoice number, filter by status, and open one to update shipping.",
                href: "/admin/transactions",
                icon: ReceiptTextIcon,
            },
        ],
    },
];

export default function AdminDashboard() {
    return (
        <>
            <Head title="Dashboard" />

            <AdminLayout>
                <header className="mb-10">
                    <h1 className="text-center text-2xl font-bold sm:text-3xl">
                        Dashboard
                    </h1>
                </header>

                <div className="mx-auto flex max-w-250 flex-col gap-8">
                    {quickActions.map(({ section, actions }) => (
                        <section key={section} className="flex flex-col gap-4">
                            <h2 className="font-heading text-lg font-semibold">
                                {section}
                            </h2>

                            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                {actions.map((action) => {
                                    const Icon = action.icon;

                                    return (
                                        <InertiaLink
                                            key={action.href}
                                            href={action.href}
                                            className="group block rounded-xl focus-visible:outline-none"
                                        >
                                            <Card className="group-hover:bg-muted/50 group-focus-visible:ring-ring/50 h-full gap-4 transition group-focus-visible:ring-3">
                                                <CardContent className="flex flex-col gap-3">
                                                    <div className="bg-primary/10 text-primary flex size-10 items-center justify-center rounded-lg">
                                                        <Icon className="size-5" />
                                                    </div>

                                                    <div className="flex flex-col gap-1">
                                                        <CardTitle className="flex items-center justify-between gap-2">
                                                            {action.title}
                                                            <ArrowRightIcon className="text-muted-foreground size-4 shrink-0 transition-transform group-hover:translate-x-0.5" />
                                                        </CardTitle>

                                                        <CardDescription>
                                                            {action.description}
                                                        </CardDescription>
                                                    </div>
                                                </CardContent>
                                            </Card>
                                        </InertiaLink>
                                    );
                                })}
                            </div>
                        </section>
                    ))}
                </div>
            </AdminLayout>
        </>
    );
}
