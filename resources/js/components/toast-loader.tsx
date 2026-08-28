import { Toaster } from "@/components/ui/sonner";
import { FlashProps } from "@/types/inertia-props";
import { usePage } from "@inertiajs/react";
import { useEffect } from "react";
import { toast } from "sonner";

export function ToastLoader() {
    const { flash } = usePage<FlashProps>().props;
    const toastOptions = { position: "top-right" as const };

    useEffect(() => {
        if (!flash?.toast) return;

        const { type, message } = flash.toast;

        if (type === "success") {
            toast.success(message, toastOptions);
        } else if (type === "error") {
            toast.error(message, toastOptions);
        } else {
            toast(message, toastOptions);
        }
    }, [flash]);

    return (
        <>
            <Toaster />
        </>
    );
}
