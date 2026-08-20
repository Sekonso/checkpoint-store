import {
    Field,
    FieldDescription,
    FieldLabel,
    FieldError,
} from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import Asterisk from "@/components/Asterisk";
import { InputHTMLAttributes } from "react";

interface InputFieldProps extends InputHTMLAttributes<HTMLInputElement> {
    name: string;
    label: string;
    description?: string;
    required?: boolean;
    error?: boolean;
    errorMessage?: string;
}

export function InputField({
    name,
    label,
    description,
    required = false,
    error,
    errorMessage = "",
    id,
    ...props
}: InputFieldProps) {
    return (
        <Field>
            <FieldLabel htmlFor={id}>
                {label}

                {required && <Asterisk />}
            </FieldLabel>

            <Input id={id} name={name} {...props} aria-invalid={error} />

            {error && <FieldError>{errorMessage}</FieldError>}

            {description && <FieldDescription>{description}</FieldDescription>}
        </Field>
    );
}
