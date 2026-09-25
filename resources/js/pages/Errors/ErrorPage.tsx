import ErrorLayout from "@/layouts/ErrorLayout";
import { Link } from "@inertiajs/react";

interface ErrorPageProps {
    status: number;
}

interface ErrorInfo {
    title: string;
    description: string;
}

const errorMessages: Record<number, ErrorInfo> = {
    400: {
        title: "Bad Request",
        description:
            "Your request could not be processed. Please check your input and try again.",
    },
    401: {
        title: "Unauthorized",
        description: "You need to sign in to access this page.",
    },
    403: {
        title: "Forbidden",
        description: "You are forbidden from accessing this page.",
    },
    404: {
        title: "Page Not Found",
        description: "The page you are looking for could not be found.",
    },
    405: {
        title: "Method Not Allowed",
        description: "The method used to access this page is not allowed.",
    },
    419: {
        title: "Page Expired",
        description:
            "Your session has expired. Please go back to home and try again.",
    },
    422: {
        title: "Unprocessable Content",
        description:
            "We could not process your submission. Please review it and try again.",
    },
    429: {
        title: "Too Many Requests",
        description:
            "You have made too many requests. Please wait a moment and try again.",
    },
    500: {
        title: "Server Error",
        description:
            "Something went wrong on our servers. Please try again later.",
    },
    503: {
        title: "Service Unavailable",
        description: "The server is under maintenance. Please check back soon.",
    },
};

function getErrorInfo(status: number): ErrorInfo {
    return (
        errorMessages[status] ??
        (status >= 500
            ? {
                  title: "Server Error",
                  description:
                      "Something went wrong on our servers. Please try again later.",
              }
            : {
                  title: "Request Error",
                  description:
                      "Something went wrong with your request. Please try again.",
              })
    );
}

export default function ErrorPage({ status }: ErrorPageProps) {
    const { title, description } = getErrorInfo(status);

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
