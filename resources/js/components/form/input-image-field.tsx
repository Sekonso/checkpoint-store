import { FieldDescription } from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import { FieldWrapper } from "@/components/form/field-wrapper";
import { InputHTMLAttributes, useEffect, useState } from "react";

interface ImageFieldProps extends Omit<
    InputHTMLAttributes<HTMLInputElement>,
    "type"
> {
    id: string;
    name: string;
    label: string;
    defaultPreview?: string;
    description?: string;
    required?: boolean;
    isError?: boolean;
    errorMessage?: string;
}

export function InputImageField({
    id,
    name,
    label,
    defaultPreview,
    description,
    required,
    isError,
    errorMessage,
    ...props
}: ImageFieldProps) {
    const [preview, setPreview] = useState<string | null>(
        defaultPreview ?? null,
    );

    const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];

        if (file) {
            setPreview(URL.createObjectURL(file));
        } else {
            setPreview(null);
        }

        props.onChange?.(event);
    };

    useEffect(() => {
        return () => {
            if (preview?.startsWith("blob:")) {
                URL.revokeObjectURL(preview);
            }
        };
    }, [preview]);

    return (
        <FieldWrapper
            id={id}
            label={label}
            required={required}
            isError={isError}
            errorMessage={errorMessage}
        >
            <div className="space-y-3">
                {preview && (
                    <div className="overflow-hidden rounded-md border">
                        <img
                            src={preview}
                            alt={`${label} preview`}
                            className="h-60 w-full object-cover"
                        />
                    </div>
                )}

                <Input
                    {...props}
                    type="file"
                    accept="image/*"
                    id={id}
                    name={name}
                    aria-invalid={isError}
                    aria-required={required}
                    onChange={handleChange}
                />
            </div>

            {description && <FieldDescription>{description}</FieldDescription>}
        </FieldWrapper>
    );
}
