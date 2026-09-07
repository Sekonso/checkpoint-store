import { router } from "@inertiajs/react";
import { cn } from "@/lib/utils";
import {
    NativeSelect,
    NativeSelectOption,
} from "@/components/ui/native-select";

export interface FilterOption {
    value: string;
    label: string;
}

interface FilterBarProps {
    param: string;
    url: string;
    initialValue?: string;
    options: FilterOption[];
    size?: "small" | "medium";
    className?: string;
    selectClassName?: string;
}

export function FilterBar({
    param,
    url,
    initialValue = "",
    options,
    className,
    selectClassName,
}: FilterBarProps) {
    function filterChangeHandler(value: string) {
        const params = new URLSearchParams(window.location.search);

        if (value) {
            params.set(param, value);
        } else {
            params.delete(param);
        }

        params.delete("page");

        const query = params.toString();

        router.get(query ? `${url}?${query}` : url, {
            replace: true,
        });
    }

    return (
        <NativeSelect
            value={initialValue}
            onChange={(e) => filterChangeHandler(e.target.value)}
            className={cn("border-0", className)}
            selectClassName={selectClassName}
        >
            {options.map((option) => (
                <NativeSelectOption key={option.value} value={option.value}>
                    {option.label}
                </NativeSelectOption>
            ))}
        </NativeSelect>
    );
}
