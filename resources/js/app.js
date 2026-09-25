import { createInertiaApp } from "@inertiajs/react";

createInertiaApp({
    pages: {
        path: "./pages",
        extension: ".tsx",
    },
    title: (title) =>
        title ? `${title} - Checkpoint Store` : "Checkpoint Store",
});
