import { Link as InertiaLink, usePage } from "@inertiajs/react";
import { Link } from "@/components/Link";
import { Button } from "@/components/ui/button";
import {
    Sheet,
    SheetContent,
    SheetFooter,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from "@/components/ui/sheet";
import { Menu } from "lucide-react";

const headerLinks = [
    { name: "Home", href: "/" },
    { name: "Store", href: "/store" },
    { name: "Blog", href: "/blog" },
    { name: "About", href: "/about" },
    { name: "FAQ", href: "/faq" },
];

export default function Header() {
    const { url: activePage } = usePage();

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
                    <Button className="border-primary bg-transparent">
                        Login
                    </Button>
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
                            <li>
                                <InertiaLink href="/">
                                    <Button
                                        variant="ghost"
                                        className="w-full justify-start"
                                    >
                                        Home
                                    </Button>
                                </InertiaLink>
                            </li>
                            <li>
                                <InertiaLink href="/about">
                                    <Button
                                        variant="ghost"
                                        className="w-full justify-start"
                                    >
                                        About
                                    </Button>
                                </InertiaLink>
                            </li>
                        </ul>
                        <SheetFooter>
                            <Button>Login</Button>
                        </SheetFooter>
                    </SheetContent>
                </Sheet>
            </div>
        </header>
    );
}
