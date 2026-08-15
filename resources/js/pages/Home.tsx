import { Head } from "@inertiajs/react";
import MainLayout from "@/layouts/MainLayout";
import Hero from "@/components/blocks/Hero";
import ReviewCarousel from "@/components/ReviewCarousel";

export default function Home() {
    return (
        <>
            <Head title="Home" />

            <MainLayout>
                <Hero />
                <ReviewCarousel />
            </MainLayout>
        </>
    );
}
