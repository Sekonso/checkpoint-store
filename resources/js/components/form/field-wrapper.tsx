import { ReactNode } from "react";
import {
    Field,
    FieldLabel,
    FieldError,
} from "@/components/ui/field";
import Asterisk from "@/components/asterisk";

interface FieldWrapperProps {
    id: string;
    label: string;
    required?: boolean;
    isError?: boolean;
    errorMessage?: string;
    children: ReactNode;
}

export function FieldWrapper(props: FieldWrapperProps) {
    return (
        <Field>
            <FieldLabel htmlFor={props.id}>
                {props.label}

                {props.required && <Asterisk />}
            </FieldLabel>

            {props.children}

            {props.isError && <FieldError>{props.errorMessage}</FieldError>}
        </Field>
    );
}
