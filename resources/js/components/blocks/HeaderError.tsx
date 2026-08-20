import { Link as InertiaLink } from "@inertiajs/react";

export default function HeaderError() {
    // Render
    return (
        <header className="bg-popover sticky top-0 z-99">
            <div className="wrapper flex items-center justify-center py-4">
                {/* BRAND */}
                <InertiaLink className="" href="/">
                    <span className="font-heading text-center text-lg font-bold uppercase">
                        Checkpoint Store
                    </span>
                </InertiaLink>
            </div>
        </header>
    );
}
