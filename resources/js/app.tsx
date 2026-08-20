import { createInertiaApp } from "@inertiajs/react";

createInertiaApp({
    pages: {
        path: "./Pages",
        extension: ".tsx",
    },
    title: (title) =>
        title ? `${title} - Checkpoint Store` : "Checkpoint Store",
});
