import { FieldDescription } from "@/components/ui/field";
import { TextareaHTMLAttributes } from "react";
import { FieldWrapper } from "@/components/form/field-wrapper";
import { Textarea } from "@/components/ui/textarea";

interface TextAreaFieldProps extends TextareaHTMLAttributes<HTMLTextAreaElement> {
    id: string;
    name: string;
    label: string;
    description?: string;
    required?: boolean;
    isError?: boolean;
    errorMessage?: string;
}

export function TextAreaField({
    id,
    name,
    label,
    description,
    required = false,
    isError = false,
    errorMessage = "",
    ...props
}: TextAreaFieldProps) {
    return (
        <FieldWrapper
            id={id}
            label={label}
            required={required}
            isError={isError}
            errorMessage={errorMessage}
        >
            <Textarea
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
