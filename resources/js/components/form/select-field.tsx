import { FieldDescription } from "@/components/ui/field";
import {
    NativeSelect,
    NativeSelectOption,
} from "@/components/ui/native-select";
import { SelectHTMLAttributes } from "react";
import { FieldWrapper } from "@/components/form/field-wrapper";

export type SelectFieldOptions = {
    name: string;
    value: string;
}[];

interface SelectFieldProps extends Omit<
    SelectHTMLAttributes<HTMLSelectElement>,
    "size"
> {
    id: string;
    name: string;
    label: string;
    options: SelectFieldOptions;
    defaultValue?: string;
    placeholder?: string;
    description?: string;
    required?: boolean;
    isError?: boolean;
    errorMessage?: string;
}

export function SelectField({
    id,
    name,
    label,
    options,
    defaultValue,
    placeholder,
    description,
    required = false,
    isError = false,
    errorMessage = "",
    ...props
}: SelectFieldProps) {
    return (
        <FieldWrapper
            id={id}
            label={label}
            required={required}
            isError={isError}
            errorMessage={errorMessage}
        >
            <NativeSelect
                {...props}
                id={id}
                name={name}
                defaultValue={defaultValue}
                aria-invalid={isError}
                aria-required={required}
            >
                {/* placeholder */}
                {placeholder && (
                    <NativeSelectOption value="" disabled>
                        {placeholder}
                    </NativeSelectOption>
                )}

                {/* options */}
                {options.map(({ name, value }) => (
                    <NativeSelectOption key={value} value={value}>
                        {name}
                    </NativeSelectOption>
                ))}
            </NativeSelect>

            {description && <FieldDescription>{description}</FieldDescription>}
        </FieldWrapper>
    );
}
