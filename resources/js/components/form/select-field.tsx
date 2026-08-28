import {
    Field,
    FieldDescription,
    FieldLabel,
    FieldError,
} from "@/components/ui/field";
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
    description,
    required,
    isError,
    errorMessage,
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
                {options.map(({ name, value }, idx) => (
                    <NativeSelectOption
                        key={idx}
                        value={value}
                    >
                        {name}
                    </NativeSelectOption>
                ))}
            </NativeSelect>

            {description && <FieldDescription>{description}</FieldDescription>}
        </FieldWrapper>
    );
}
