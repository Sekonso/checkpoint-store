export default function StoreIntro() {
    return (
        <section id="store-intro" className="wrapper scroll-mt-24 py-16">
            <div className="grid items-center gap-10 md:grid-cols-2">
                {/* Image */}
                <img
                    src="/images/silkbone.webp"
                    alt=""
                    aria-hidden="true"
                    width={639}
                    height={444}
                    loading="lazy"
                    className="order-2 mx-auto w-full max-w-sm object-contain md:order-1"
                />

                {/* Content */}
                <div className="order-1 flex flex-col gap-4 md:order-2">
                    <p className="text-primary text-sm font-medium tracking-wider uppercase">
                        What is Checkpoint Store?
                    </p>
                    <h2 className="font-heading text-2xl font-bold tracking-wide uppercase sm:text-3xl">
                        Provider For High Quality Gaming Gear
                    </h2>
                    <p className="text-muted-foreground leading-relaxed">
                        Checkpoint Store is a gaming gear store for players who
                        care about how their setup feels. We provide high
                        quality gears to enchance your gaming experience to the
                        next level.
                    </p>
                </div>
            </div>
        </section>
    );
}
