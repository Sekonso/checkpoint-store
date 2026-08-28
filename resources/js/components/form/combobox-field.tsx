import {
    Combobox,
    ComboboxChip,
    ComboboxChips,
    ComboboxChipsInput,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxItem,
    ComboboxList,
    ComboboxValue,
    useComboboxAnchor,
} from "@/components/ui/combobox";
import { FieldWrapper } from "@/components/form/field-wrapper";
import { FieldDescription } from "@/components/ui/field";
import { useState } from "react";

export type ComboboxFieldOptions = {
    name: string;
    value: string;
}[];

interface ComboboxFieldProps {
    id: string;
    name: string;
    label: string;
    options: ComboboxFieldOptions;
    defaultValue?: string[];
    placeholder?: string;
    description?: string;
    required?: boolean;
    isError?: boolean;
    errorMessage?: string;
}

export function ComboboxField({
    id,
    name,
    label,
    options,
    defaultValue,
    placeholder,
    description,
    required,
    isError,
    errorMessage,
}: ComboboxFieldProps) {
    const anchor = useComboboxAnchor();

    const [value, setValue] = useState<string[]>(defaultValue ?? []);

    return (
        <FieldWrapper
            id={id}
            label={label}
            required={required}
            isError={isError}
            errorMessage={errorMessage}
        >
            <Combobox
                name={name}
                multiple
                autoHighlight
                items={options}
                value={value}
                onValueChange={setValue}
            >
                <ComboboxChips ref={anchor}>
                    <ComboboxValue>
                        {value.map((selectedValue) => {
                            const option = options.find(
                                (option) => option.value === selectedValue,
                            );

                            if (!option) {
                                return null;
                            }

                            return (
                                <ComboboxChip key={option.value}>
                                    {option.name}
                                </ComboboxChip>
                            );
                        })}
                    </ComboboxValue>

                    <ComboboxChipsInput
                        placeholder={placeholder}
                        aria-invalid={isError}
                    />
                </ComboboxChips>

                <ComboboxContent anchor={anchor}>
                    <ComboboxEmpty>No items found.</ComboboxEmpty>

                    <ComboboxList>
                        {(option: { name: string; value: string }) => (
                            <ComboboxItem
                                key={option.value}
                                value={option.value}
                            >
                                {option.name}
                            </ComboboxItem>
                        )}
                    </ComboboxList>
                </ComboboxContent>
            </Combobox>

            {description && <FieldDescription>{description}</FieldDescription>}
        </FieldWrapper>
    );
}
