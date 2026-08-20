import ErrorLayout from "@/layouts/ErrorLayout";
import { Link, usePage } from "@inertiajs/react";

type ErrorStatus = 403 | 404 | 500 | 503;

interface ErrorPageProps {
    status: ErrorStatus;
}

const errorTitles: Record<ErrorStatus, string> = {
    403: "Forbidden",
    404: "Page Not Found",
    500: "Server Error",
    503: "Service Unavailable",
};

const errorDescriptions: Record<ErrorStatus, string> = {
    403: "Sorry, you are forbidden from accessing this page.",
    404: "Sorry, the page you are looking for could not be found.",
    500: "Whoops, something went wrong on our servers.",
    503: "Sorry, we are doing some maintenance. Please check back soon.",
};

export default function ErrorPage({ status }: ErrorPageProps) {
    const page = usePage();
    console.log("page data: ", page);

    const title = errorTitles[status];
    const description = errorDescriptions[status];

    return (
        <ErrorLayout>
            <div className="wrapper flex flex-col items-center py-25">
                <div className="flex flex-col items-center gap-2">
                    <h1 className="text-primary text-center font-bold">
                        {status}
                    </h1>

                    <span className="font-heading text-center text-3xl font-bold">
                        {title}
                    </span>

                    <h1 className="text-muted-foreground text-center text-lg font-bold">
                        {description}
                    </h1>
                </div>

                <Link
                    href="/"
                    className="text-primary mt-4 text-center font-semibold"
                >
                    Back to home
                </Link>
            </div>
        </ErrorLayout>
    );
}
