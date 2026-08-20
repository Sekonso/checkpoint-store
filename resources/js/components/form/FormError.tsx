import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { CircleAlert } from "lucide-react";

interface FormErrorProps {
    title?: string;
    message?: string;
}

export function FormError({
    title = "Submission failed",
    message = "An error occurred. Please try again.",
}: FormErrorProps) {
    return (
        <Alert className="mt-4 max-w-md border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-50">
            <CircleAlert />

            <AlertTitle>{title}</AlertTitle>

            <AlertDescription>{message}</AlertDescription>
        </Alert>
    );
}
