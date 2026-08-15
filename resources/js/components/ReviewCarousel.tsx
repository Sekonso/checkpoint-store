"use client";

import * as React from "react";
import Autoplay from "embla-carousel-autoplay";

import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from "@/components/ui/carousel";
import { Card, CardContent } from "@/components/ui/card";
import { Star } from "lucide-react";

const reviews = [
    {
        name: "Sarah Wijaya",
        role: "Customer",
        review: "Great products and excellent service. Everything arrived quickly and was exactly what I expected.",
        rating: 5,
    },
    {
        name: "Andi Pratama",
        role: "Customer",
        review: "Really happy with the quality. The ordering process was simple and the team was very helpful.",
        rating: 5,
    },
    {
        name: "Nadia Putri",
        role: "Customer",
        review: "A great experience from start to finish. Definitely coming back for my next purchase.",
        rating: 5,
    },
    {
        name: "Rizky Ramadhan",
        role: "Customer",
        review: "The product exceeded my expectations. Fast delivery and great attention to detail.",
        rating: 5,
    },
];

export default function ReviewCarousel() {
    const plugin = React.useRef(
        Autoplay({
            delay: 4000,
            stopOnInteraction: true,
        }),
    );

    return (
        <section className="wrapper py-16">
            <div className="mb-10 text-center">
                <p className="text-primary mb-2 text-sm font-medium tracking-wider uppercase">
                    Testimonials
                </p>

                <h2 className="text-3xl font-bold md:text-4xl">
                    What our customers say
                </h2>

                <p className="text-muted-foreground mx-auto mt-3 max-w-xl">
                    See what our customers have to say about their experience.
                </p>
            </div>

            <Carousel
                plugins={[plugin.current]}
                opts={{
                    align: "start",
                    loop: true,
                }}
                className="mx-auto w-full px-4"
                onMouseEnter={plugin.current.stop}
                onMouseLeave={plugin.current.reset}
            >
                <CarouselContent className="-ml-4">
                    {reviews.map((review) => (
                        <CarouselItem
                            key={review.name}
                            className="pl-4 md:basis-1/2 lg:basis-1/3"
                        >
                            <Card className="bg-card h-full border transition-transform duration-300 hover:-translate-y-1">
                                <CardContent className="flex h-full flex-col p-6">
                                    <div className="mb-4 flex gap-1">
                                        {Array.from({
                                            length: review.rating,
                                        }).map((_, index) => (
                                            <Star
                                                key={index}
                                                className="fill-primary text-primary size-4"
                                            />
                                        ))}
                                    </div>

                                    <p className="text-muted-foreground flex-1 leading-relaxed">
                                        “{review.review}”
                                    </p>

                                    <div className="mt-6">
                                        <p className="font-semibold">
                                            {review.name}
                                        </p>
                                        <p className="text-muted-foreground text-sm">
                                            {review.role}
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>
                        </CarouselItem>
                    ))}
                </CarouselContent>

                <CarouselPrevious className="-left-4" />
                <CarouselNext className="-right-4" />
            </Carousel>
        </section>
    );
}
