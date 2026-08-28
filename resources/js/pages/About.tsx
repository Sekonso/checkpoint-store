import MainLayout from "@/layouts/MainLayout";
import { Head } from "@inertiajs/react";

export default function About() {
    return (
        <>
            <Head title="About" />

            <MainLayout>
                <div className="wrapper">
                    {/* Head */}
                    <section className="bg-red-gradient">
                        <h1 className="font-heading py-12 text-center text-4xl font-bold tracking-wider text-white uppercase">
                            About Us
                        </h1>
                    </section>

                    {/* About */}
                    <section className="grid gap-12 py-8 sm:py-16 md:grid-cols-2 md:items-center">
                        <div className="space-y-4">
                            <h2 className="font-heading text-2xl font-bold tracking-wide uppercase sm:text-3xl">
                                Rest Up. Ready your gear before next
                                destinations
                            </h2>

                            <p className="text-muted-foreground leading-relaxed">
                                Checkpoint Store provides reliable equipments to
                                enhance your setup and gaming experience. From
                                gaming peripherals and accessories to gear for
                                dedicated setups, we make it easier to find the
                                equipment you need. Whether you're building your
                                first gaming station or upgrading your current
                                setup, Checkpoint Store is here to help.
                            </p>
                        </div>

                        <img
                            src="/images/about.webp"
                            alt="Hollow knight imaeg"
                            className="h-full w-full object-cover"
                        />
                    </section>
                </div>
            </MainLayout>
        </>
    );
}
