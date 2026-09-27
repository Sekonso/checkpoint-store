import { CoordinateBox } from "@/components/coordinate-box";
import { Link as InertiaLink } from "@inertiajs/react";
import { Separator } from "../ui/separator";

const footerNavigations = {
    navigation: [
        { name: "Home", href: "/" },
        { name: "About", href: "/about" },
        { name: "FAQ", href: "/faq" },
    ],
    products: [
        { name: "All Products", href: "/products" },
        { name: "Featured", href: "/products/featured" },
        { name: "New Arrivals", href: "/products/new" },
    ],
    contacts: [
        { name: "Whatsapp", href: "https://api.whatsapp.com/send?phone=6282123615939" },
        { name: "Email", href: "mailto:ancaadri9@gmail.com" },
        { name: "Github", href: "https://github.com/Sekonso" },
    ],
};

export default function Footer() {
    return (
        <footer>
            <div className="wrapper pt-8">
                {/* UPPER */}
                <div className="mb-8">
                    {/* Brand */}
                    <InertiaLink href="" className="flex items-baseline gap-2">
                        <span className="font-heading text-xl font-bold uppercase sm:text-3xl">
                            Checkpoint Store
                        </span>
                        <span className="text-primary sm:text-md font-heading text-sm font-bold">
                            Ready your gear.
                        </span>
                    </InertiaLink>
                </div>

                {/* CONTENT */}
                <div className="grid grid-cols-1 gap-8 sm:grid-cols-[1fr_2fr]">
                    {/* Location */}
                    <div>
                        <CoordinateBox className="h-35 w-full" />
                        <div className="my-4"></div>
                        <a
                            href="https://www.google.com/maps/place/The+Park+Sawangan/data=!4m2!3m1!1s0x0:0x61c6618db8b06e?sa=X&ved=1t:2428&ictx=111"
                            target="_blank"
                            className="text-muted-foreground hover:text-foreground"
                        >
                            Jl. Raya Parung - Ciputat No.1, RT.6/RW.004, Serua,
                            Kec. Bojongsari, Kota Depok, Jawa Barat 16517
                        </a>
                    </div>

                    {/* Navigations */}
                    <div className="flex flex-col gap-6 sm:flex-row sm:justify-around">
                        {Object.entries(footerNavigations).map(
                            ([title, items]) => (
                                <div key={title}>
                                    <h4 className="font-semibold capitalize">
                                        {title}
                                    </h4>

                                    <ul className="text-muted-foreground mt-2 space-y-2">
                                        {items.map((item) => (
                                            <li key={item.name}>
                                                <InertiaLink
                                                    href={item.href}
                                                    className="text-muted-foreground hover:text-foreground"
                                                >
                                                    {item.name}
                                                </InertiaLink>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            ),
                        )}
                    </div>
                </div>

                {/* Attribution */}
                <div className="flex items-center justify-center pt-8 sm:justify-between">
                    <div className="text-muted-foreground w-full text-sm whitespace-nowrap">
                        <Separator className="mobile-only w-full" />
                        <div className="my-4 w-full text-center sm:text-left">
                            Demo website - Sekonso
                        </div>
                    </div>
                    <img
                        src="/images/controller.png"
                        className="table-desktop-only max-h-25 object-cover"
                    ></img>
                </div>
            </div>
        </footer>
    );
}
