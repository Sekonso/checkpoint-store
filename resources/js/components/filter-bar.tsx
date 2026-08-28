import { useForm } from "@inertiajs/react";
import { useEffect, useState } from "react";
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
    const [selectedValue, setSelectedValue] = useState(initialValue);
    const { get } = useForm();

    useEffect(() => {
        const params = new URLSearchParams(window.location.search);

        if (selectedValue) {
            params.set(param, selectedValue);
        } else {
            params.delete(param);
        }

        params.delete("page");

        get(`${url}?${params.toString()}`, {
            preserveState: true,
            preserveScroll: true,
        });
    }, [selectedValue]);

    return (
        <NativeSelect
            value={selectedValue}
            onChange={(e) => setSelectedValue(e.target.value)}
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
