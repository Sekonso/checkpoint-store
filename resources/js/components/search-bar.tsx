import { cn } from "@/lib/utils";
import { router } from "@inertiajs/react";
import { SubmitEvent, useState } from "react";

interface SearchBarProps {
    param: string;
    url: string;
    initialValue?: string;
    size?: "small" | "medium";
    placeholder?: string;
    buttonLabel?: string;
    className?: string;
    inputClassName?: string;
    buttonClassName?: string;
}

export function SearchBar({
    param,
    url,
    initialValue = "",
    placeholder = "Search article title...",
    buttonLabel = "Search",
    className,
    inputClassName,
    buttonClassName,
}: SearchBarProps) {
    const [searchValue, setSearchValue] = useState<string>(initialValue || "");

    const submitHandler = (e: SubmitEvent) => {
        e.preventDefault();

        const params = new URLSearchParams(window.location.search);

        if (searchValue.trim()) {
            params.set(param, searchValue);
        } else {
            params.delete(param);
        }

        params.delete("page");

        router.get(`${url}?${params.toString()}`, {
            replace: true,
        });
    };

    return (
        <form
            onSubmit={submitHandler}
            className={cn(
                "bg-card text-secondary-foreground flex justify-between overflow-hidden rounded-sm border",
                className,
            )}
        >
            <input
                value={searchValue}
                onChange={(e) => setSearchValue(e.target.value)}
                placeholder={placeholder}
                className={cn(
                    "text-secondary-foreground flex-1 px-4",
                    inputClassName,
                )}
            />
            <button
                type="submit"
                className={cn(
                    "bg-primary text-primary-foreground font-heading hover:bg-primary/50 px-8 font-semibold",
                    buttonClassName,
                )}
            >
                {buttonLabel}
            </button>
        </form>
    );
}
