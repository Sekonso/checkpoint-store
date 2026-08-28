import AuthLayout from "@/layouts/AuthLayout";
import { Card } from "@/components/ui/card";
import { FieldGroup, FieldLegend, FieldSet } from "@/components/ui/field";
import { Button } from "@/components/ui/button";
import {
    Link as InertiaLink,
    Form as InertiaForm,
    usePage,
    Head,
} from "@inertiajs/react";
import { InputField } from "@/components/form/input-field";
import { FormError } from "@/components/form/form-error";
import { FlashProps } from "@/types/inertia-props";

export default function SignUp() {
    const { flash } = usePage<FlashProps>().props;

    // Render
    return (
        <>
            <Head title="Sign In" />

            <AuthLayout>
                <Card className="w-full max-w-120 p-8">
                    <InertiaForm action="/sign-in" method="post">
                        {({ errors, processing }) => {
                            return (
                                <>
                                    <FieldSet>
                                        {/* Header */}
                                        <FieldLegend className="mt-4 mb-6 flex w-full items-center justify-center">
                                            <h1 className="font-heading text-2xl font-bold uppercase">
                                                Login
                                            </h1>
                                        </FieldLegend>

                                        {/* Input group */}
                                        <FieldGroup>
                                            {/* Username */}
                                            <InputField
                                                id="name"
                                                name="name"
                                                label="Username"
                                                placeholder="My username"
                                                autoComplete="username"
                                                isError={Boolean(errors.name)}
                                                errorMessage={errors.name}
                                                required
                                            />

                                            {/* Password */}
                                            <InputField
                                                id="password"
                                                name="password"
                                                label="Password"
                                                type="password"
                                                placeholder="My secret password"
                                                isError={Boolean(
                                                    errors.password,
                                                )}
                                                errorMessage={errors.password}
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
                                        {processing
                                            ? "Submitting..."
                                            : "Submit"}
                                    </Button>

                                    <div className="text-muted-foreground mt-4 flex justify-center gap-1">
                                        <span>Don't have an account?</span>
                                        <InertiaLink href="/sign-up">
                                            <span className="text-foreground">
                                                sign up
                                            </span>
                                        </InertiaLink>
                                    </div>
                                </>
                            );
                        }}
                    </InertiaForm>
                </Card>
            </AuthLayout>
        </>
    );
}
