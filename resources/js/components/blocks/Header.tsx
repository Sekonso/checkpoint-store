import {
    Link as InertiaLink,
    Form as InertiaForm,
    usePage,
} from "@inertiajs/react";
import { Link } from "@/components/link";
import { Button } from "@/components/ui/button";
import {
    Sheet,
    SheetContent,
    SheetFooter,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from "@/components/ui/sheet";
import { Menu, ShoppingBasket, User } from "lucide-react";
import { AuthProps } from "@/types/inertia-props";
import {
    HoverCard,
    HoverCardContent,
    HoverCardTrigger,
} from "@/components/ui/hover-card";
import { Separator } from "@/components/ui/separator";

const headerLinks = [
    { name: "Home", href: "/" },
    { name: "Store", href: "/store" },
    { name: "Blog", href: "/blog" },
    { name: "About", href: "/about" },
    { name: "FAQ", href: "/faq" },
];

const userLinks = [
    { name: "Profile", href: "/profile" },
    { name: "My Cart", href: "/cart" },
    { name: "Purchase History", href: "/purchases" },
];

export default function Header() {
    const { url: activePage, props } = usePage<AuthProps>();
    const { user: authUser } = props.auth;

    // Render
    return (
        <header className="bg-background sticky top-0 z-99">
            <div className="wrapper flex items-center justify-between py-4">
                {/* BRAND */}
                <InertiaLink className="" href="/">
                    <span className="font-heading text-lg font-bold uppercase">
                        Checkpoint Store
                    </span>
                </InertiaLink>

                {/* NAV */}
                <nav className="desktop-only">
                    <ul className="flex items-center gap-8">
                        {headerLinks.map((link, idx) => {
                            return (
                                <li key={idx}>
                                    <Link
                                        href={link.href}
                                        className={
                                            activePage === link.href
                                                ? "text-primary"
                                                : ""
                                        }
                                    >
                                        {link.name}
                                    </Link>
                                </li>
                            );
                        })}
                    </ul>
                </nav>

                {/* USER */}
                <div className="desktop-only">
                    {authUser ? (
                        <div className="flex gap-2">
                            <InertiaLink href="/cart">
                                <Button className="border-primary bg-transparent">
                                    <ShoppingBasket />
                                </Button>
                            </InertiaLink>
                            <HoverCard>
                                <HoverCardTrigger
                                    delay={100}
                                    closeDelay={200}
                                    render={
                                        <InertiaLink href="/profile">
                                            <Button className="border-primary bg-transparent">
                                                <User />
                                            </Button>
                                        </InertiaLink>
                                    }
                                ></HoverCardTrigger>
                                <HoverCardContent className="flex flex-col gap-4 p-4">
                                    <div className="flex flex-col">
                                        <span className="text-lg font-semibold">
                                            {authUser.name}
                                        </span>
                                        <span className="text-muted-foreground text-xs">
                                            {authUser.email}
                                        </span>
                                    </div>
                                    <Separator />
                                    <ul className="text-muted-foreground flex flex-col gap-2">
                                        {userLinks.map((item, idx) => (
                                            <li key={idx}>
                                                <InertiaLink
                                                    href={item.href}
                                                    className="hover:text-foreground"
                                                >
                                                    {item.name}
                                                </InertiaLink>
                                            </li>
                                        ))}
                                        {authUser.role === "admin" && (
                                            <li>
                                                <InertiaLink
                                                    href="/admin/dashboard"
                                                    className="hover:text-foreground"
                                                >
                                                    Admin Dashbaord
                                                </InertiaLink>
                                            </li>
                                        )}
                                    </ul>
                                    <Separator />
                                    <div>
                                        <InertiaForm
                                            action="/sign-out"
                                            method="post"
                                        >
                                            <Button type="submit">
                                                Sign Out
                                            </Button>
                                        </InertiaForm>
                                    </div>
                                </HoverCardContent>
                            </HoverCard>
                        </div>
                    ) : (
                        <InertiaLink href="/sign-in">
                            <Button className="border-primary bg-transparent">
                                Sign In
                            </Button>
                        </InertiaLink>
                    )}
                </div>

                {/* MOBILE MENU */}
                <Sheet>
                    <SheetTrigger
                        className="mobile-tablet-only"
                        render={
                            <Button variant="outline">
                                <Menu />
                            </Button>
                        }
                    />
                    <SheetContent className="z-999 p-4">
                        <SheetHeader>
                            <SheetTitle className="font-heading text-xl font-bold uppercase">
                                Checkpoint
                            </SheetTitle>
                        </SheetHeader>
                        <ul>
                            {headerLinks.map((item, idx) => (
                                <li key={idx}>
                                    <InertiaLink href={item.href}>
                                        <Button
                                            variant="ghost"
                                            className="w-full justify-start"
                                        >
                                            {item.name}
                                        </Button>
                                    </InertiaLink>
                                </li>
                            ))}
                        </ul>
                        {authUser && (
                            <>
                                <Separator />
                                <ul>
                                    {userLinks.map((item, idx) => (
                                        <li key={idx}>
                                            <InertiaLink href={item.href}>
                                                <Button
                                                    variant="ghost"
                                                    className="w-full justify-start"
                                                >
                                                    {item.name}
                                                </Button>
                                            </InertiaLink>
                                        </li>
                                    ))}
                                    {authUser.role === "admin" && (
                                        <li>
                                            <InertiaLink href="/admin/dashboard">
                                                <Button
                                                    variant="ghost"
                                                    className="w-full justify-start"
                                                >
                                                    Admin Dashboard
                                                </Button>
                                            </InertiaLink>
                                        </li>
                                    )}
                                </ul>
                            </>
                        )}
                        <SheetFooter>
                            {authUser ? (
                                <InertiaForm action="/sign-out" method="post">
                                    <Button type="submit" className="w-full">
                                        Sign Out
                                    </Button>
                                </InertiaForm>
                            ) : (
                                <InertiaLink href="/sign-in">
                                    <Button className="w-full">Sign In</Button>
                                </InertiaLink>
                            )}
                        </SheetFooter>
                    </SheetContent>
                </Sheet>
            </div>
        </header>
    );
}
