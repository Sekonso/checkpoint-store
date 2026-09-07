import AdminLayout from "@/layouts/AdminLayout";
import { FieldGroup, FieldSet } from "@/components/ui/field";
import { Button } from "@/components/ui/button";
import { InputField } from "@/components/form/input-field";
import {
    Link as InertiaLink,
    Form as InertiaForm,
    usePage,
    Head,
} from "@inertiajs/react";
import { FlashProps } from "@/types/inertia-props";
import { FormError } from "@/components/form/form-error";
import {
    SelectField,
    SelectFieldOptions,
} from "@/components/form/select-field";
import { PageProps } from "@inertiajs/core";
import { ProductCategory } from "@/types/models";
import { TextAreaField } from "@/components/form/textarea-field";

interface AdminProductsCreatePageProps extends FlashProps, PageProps {
    productCategories: ProductCategory[];
}

export default function AdminProductsCreatePage() {
    const { flash, productCategories } =
        usePage<AdminProductsCreatePageProps>().props;

    const categoriesOptions: SelectFieldOptions = productCategories.map(
        (item) => ({
            name: item.name,
            value: String(item.id),
        }),
    );

    const inDisplayOptions: SelectFieldOptions = [
        { name: "Yes", value: "1" },
        { name: "No", value: "0" },
    ];

    return (
        <>
            <Head title="New Product form" />

            <AdminLayout>
                <header className="mb-4">
                    <h1 className="text-center text-2xl font-bold sm:text-3xl">
                        New Product
                    </h1>
                </header>

                {/* Form */}
                <InertiaForm
                    action="/admin/products"
                    method="post"
                    className="mx-auto max-w-250"
                >
                    {({ errors, processing }) => {
                        return (
                            <>
                                <FieldSet>
                                    {/* Input group */}
                                    <FieldGroup>
                                        {/* Title */}
                                        <InputField
                                            id="name"
                                            name="name"
                                            label="Name"
                                            placeholder="Name for this article"
                                            isError={Boolean(errors.name)}
                                            errorMessage={errors.name}
                                            required
                                        />

                                        <div className="grid gap-x-8 gap-y-4 sm:grid-cols-2">
                                            {/* Price */}
                                            <InputField
                                                id="price"
                                                name="price"
                                                label="Price (Rp)"
                                                type="number"
                                                min={1000}
                                                placeholder="Price amount"
                                                isError={Boolean(errors.price)}
                                                errorMessage={errors.price}
                                                required
                                            />

                                            {/* Stock */}
                                            <InputField
                                                id="stock"
                                                name="stock"
                                                label="Stock"
                                                type="number"
                                                min={0}
                                                placeholder="Stock amount"
                                                isError={Boolean(errors.stock)}
                                                errorMessage={errors.stock}
                                                required
                                            />
                                        </div>

                                        <div className="grid gap-x-8 gap-y-4 sm:grid-cols-2">
                                            {/* Category */}
                                            <SelectField
                                                id="category_id"
                                                name="category_id"
                                                label="Category"
                                                options={categoriesOptions}
                                                defaultValue=""
                                                placeholder="Select a category"
                                                isError={Boolean(
                                                    errors.category_id,
                                                )}
                                                errorMessage={
                                                    errors.category_id
                                                }
                                                required
                                            />

                                            {/* In Display */}
                                            <SelectField
                                                id="in_display"
                                                name="in_display"
                                                label="In display"
                                                defaultValue="0"
                                                options={inDisplayOptions}
                                                isError={Boolean(
                                                    errors.in_display,
                                                )}
                                                errorMessage={errors.in_display}
                                                required
                                            />
                                        </div>

                                        {/* Images */}
                                        <div className="grid gap-x-8 gap-y-4 sm:grid-cols-3">
                                            {Array.from(
                                                { length: 3 },
                                                (_, idx) => {
                                                    const index = idx + 1;

                                                    return (
                                                        <InputField
                                                            key={index}
                                                            id={`image-${index}`}
                                                            name={`image-${index}`}
                                                            label={`Image ${index}`}
                                                            type="file"
                                                            accept="image/jpeg,image/png,image/webp"
                                                            isError={Boolean(
                                                                errors[
                                                                    `image-${index}`
                                                                ],
                                                            )}
                                                            errorMessage={
                                                                errors[
                                                                    `image-${index}`
                                                                ]
                                                            }
                                                            required
                                                        />
                                                    );
                                                },
                                            )}
                                        </div>

                                        <TextAreaField
                                            id="description"
                                            name="description"
                                            label="Description"
                                            placeholder="Description for the product..."
                                            className="h-40"
                                            isError={Boolean(
                                                errors.description,
                                            )}
                                            errorMessage={errors.description}
                                            required
                                        />
                                    </FieldGroup>
                                </FieldSet>

                                {/* Generic error message */}
                                {flash.formError && (
                                    <FormError
                                        title="Submission failed"
                                        message={flash.formError}
                                    />
                                )}

                                {/* Submit */}
                                <Button
                                    type="submit"
                                    className="bg-foreground text-background hover:bg-foreground/90 mt-8 w-full py-4 hover:shadow-md active:scale-[0.98]"
                                    disabled={processing}
                                >
                                    {processing ? "Saving..." : "Save"}
                                </Button>
                            </>
                        );
                    }}
                </InertiaForm>
            </AdminLayout>
        </>
    );
}
