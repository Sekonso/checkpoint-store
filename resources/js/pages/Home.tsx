import { Head, usePage } from "@inertiajs/react";
import { PageProps } from "@inertiajs/core";
import MainLayout from "@/layouts/MainLayout";
import Hero from "@/components/blocks/Hero";
import StoreIntro from "@/components/blocks/StoreIntro";
import LatestProducts from "@/components/blocks/LatestProducts";
import LatestArticles from "@/components/blocks/LatestArticles";
import ReviewCarousel from "@/components/review-carousel";
import { Article, Product } from "@/types/models";

interface HomePageProps extends PageProps {
    latestProducts: Product[];
    latestArticles: Article[];
}

export default function Home() {
    const { latestProducts, latestArticles } = usePage<HomePageProps>().props;

    return (
        <>
            <Head title="Home" />

            <MainLayout>
                <Hero />
                <StoreIntro />
                <LatestProducts products={latestProducts} />
                <LatestArticles articles={latestArticles} />
                <ReviewCarousel />
            </MainLayout>
        </>
    );
}
