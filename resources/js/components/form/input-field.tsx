import { FieldDescription } from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import { InputHTMLAttributes } from "react";
import { FieldWrapper } from "@/components/form/field-wrapper";

interface InputFieldProps extends InputHTMLAttributes<HTMLInputElement> {
    id: string;
    name: string;
    label: string;
    description?: string;
    required?: boolean;
    isError?: boolean;
    errorMessage?: string;
}

export function InputField({
    id,
    name,
    label,
    description,
    required,
    isError,
    errorMessage,
    ...props
}: InputFieldProps) {
    return (
        <FieldWrapper
            id={id}
            label={label}
            required={required}
            isError={isError}
            errorMessage={errorMessage}
        >
            <Input
                {...props}
                id={id}
                name={name}
                aria-invalid={isError}
                aria-required={required}
            />

            {description && <FieldDescription>{description}</FieldDescription>}
        </FieldWrapper>
    );
}
