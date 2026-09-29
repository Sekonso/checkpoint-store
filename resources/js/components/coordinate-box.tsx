import { cn } from "@/lib/utils";

const EMBED_URL =
    "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3541.7764635521185!2d106.7449527!3d-6.376245199999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69ef49add5629d%3A0x61c6618db8b06e!2sThe%20Park%20Sawangan!5e1!3m2!1sen!2sid!4v1790695819248!5m2!1sen!2sid";

type CoordinateBoxProps = {
    className?: string;
    title?: string;
};

export function CoordinateBox({
    className,
    title = "The Park Sawangan location map",
}: CoordinateBoxProps) {
    return (
        <iframe
            src={EMBED_URL}
            title={title}
            loading="lazy"
            referrerPolicy="strict-origin-when-cross-origin"
            allowFullScreen
            className={cn("h-40 w-full border-0", className)}
        />
    );
}
