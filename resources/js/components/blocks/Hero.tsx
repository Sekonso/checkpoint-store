import { ChevronDown } from "lucide-react";

export default function Hero() {
    return (
        <section className="wrapper-wide relative flex h-130 items-center justify-center overflow-hidden">
            {/* Image overlay */}
            <img
                src="/images/hero.webp"
                alt="Controller"
                className="absolute inset-0 h-full w-full object-cover"
                fetchPriority="high"
            />
            <div className="absolute inset-0 bg-black/40" />

            {/* Content */}
            <div className="wrapper relative z-10 flex flex-col items-center justify-center gap-2 text-center text-white md:gap-4">
                <h1 className="font-heading text-4xl font-bold tracking-wider uppercase md:text-6xl lg:text-8xl">
                    Checkpoint
                </h1>
                <p className="font-heading text-lg uppercase md:text-xl lg:text-2xl">
                    Continue?
                </p>
                <ChevronDown className="float mt-6" size={48} />
            </div>
        </section>
    );
}
