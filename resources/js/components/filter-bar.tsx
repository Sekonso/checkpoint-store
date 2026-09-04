import { router } from "@inertiajs/react";
import { useState } from "react";
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
}

export function FilterBar({
    param,
    url,
    initialValue = "",
    options,
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
            className="border-0"
        >
            {options.map((option) => (
                <NativeSelectOption key={option.value} value={option.value}>
                    {option.label}
                </NativeSelectOption>
            ))}
        </NativeSelect>
    );
}
