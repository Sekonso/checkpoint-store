import AdminLayout from "@/layouts/AdminLayout";
import { FieldGroup, FieldLegend, FieldSet } from "@/components/ui/field";
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
import {
    ComboboxField,
    ComboboxFieldOptions,
} from "@/components/form/combobox-field";
import { SimpleEditor } from "@/components/tiptap-templates/simple/simple-editor";
import { useState } from "react";
import { InputImageField } from "@/components/form/input-image-field";
import { PageProps } from "@inertiajs/core";

interface AdminArticleCreatePageProps extends FlashProps, PageProps {
    tagOptions: SelectFieldOptions;
    statusOptions: ComboboxFieldOptions;
}

export default function AdminArticleCreatePage() {
    const { flash, tagOptions, statusOptions } =
        usePage<AdminArticleCreatePageProps>().props;
    const [content, setContent] = useState<string>("");

    return (
        <>
            <Head title="New article form" />

            <AdminLayout>
                <header className="mb-4">
                    <h1 className="text-center text-2xl font-bold sm:text-3xl">
                        New Article
                    </h1>
                </header>

                {/* Form */}
                <InertiaForm
                    action="/admin/articles"
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
                                            id="title"
                                            name="title"
                                            label="Title"
                                            placeholder="Title for this article"
                                            isError={Boolean(errors.title)}
                                            errorMessage={errors.title}
                                            required={true}
                                        />

                                        <div className="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
                                            {/* Tags */}
                                            <ComboboxField
                                                id="tags"
                                                name="tags[]"
                                                label="Tags"
                                                options={tagOptions}
                                                placeholder="Add tags"
                                                description="Minimum 1 tags"
                                                isError={Boolean(errors.tags)}
                                                errorMessage={errors.tags}
                                                required={true}
                                            />

                                            {/* Status */}
                                            <SelectField
                                                id="status"
                                                name="status"
                                                label="Status"
                                                options={statusOptions}
                                                defaultValue="draft"
                                                isError={Boolean(errors.status)}
                                                errorMessage={errors.status}
                                                required={true}
                                            />
                                        </div>

                                        {/* Featured image */}
                                        <InputImageField
                                            id="featured_image"
                                            name="featured_image"
                                            label="Featured image"
                                            description="Upload a JPG or PNG image (Max 2MB)"
                                            isError={Boolean(
                                                errors.featured_image,
                                            )}
                                            errorMessage={errors.featured_image}
                                            required={true}
                                            className="hover:cursor-pointer"
                                        />

                                        {/* Content editor */}
                                        <div className="text-foreground rounded-md border">
                                            <input
                                                type="hidden"
                                                name="content"
                                                value={content}
                                            />
                                            <SimpleEditor
                                                content={content}
                                                onContentChange={setContent}
                                            />
                                        </div>
                                    </FieldGroup>
                                </FieldSet>

                                {/* Generic error message */}
                                {(flash.formError || errors.content) && (
                                    <FormError
                                        title="Submission failed"
                                        message={
                                            flash.formError || errors.content
                                        }
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
