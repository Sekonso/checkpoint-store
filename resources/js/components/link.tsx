import { Link as InertiaLink } from "@inertiajs/react";

type LinkProps = React.ComponentProps<typeof InertiaLink>;

export function Link({ className = "", children, ...props }: LinkProps) {
    return (
        <InertiaLink
            {...props}
            className={`after:bg-primary relative after:absolute after:-bottom-1 after:left-0 after:h-0.5 after:w-full after:origin-left after:scale-x-0 after:transition-transform hover:after:scale-x-100 ${className}`}
        >
            {children}
        </InertiaLink>
    );
}
