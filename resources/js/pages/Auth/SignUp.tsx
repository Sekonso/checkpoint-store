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
            <Head title="Sign Up" />

            <AuthLayout>
                <Card className="w-full max-w-120 p-8">
                    <InertiaForm action="/sign-up" method="post">
                        {({ errors, processing }) => {
                            return (
                                <>
                                    <FieldSet>
                                        {/* Header */}
                                        <FieldLegend className="mt-4 mb-6 flex w-full items-center justify-center">
                                            <h1 className="font-heading text-2xl font-bold uppercase">
                                                Register Account
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

                                            {/* Email */}
                                            <InputField
                                                id="email"
                                                name="email"
                                                label="Email"
                                                type="email"
                                                placeholder="user@gmail.com"
                                                autoComplete="email"
                                                isError={Boolean(errors.email)}
                                                errorMessage={errors.email}
                                                required
                                            />

                                            {/* Password */}
                                            <InputField
                                                id="password"
                                                name="password"
                                                label="Password"
                                                type="password"
                                                placeholder="My secret password"
                                                description="Min 8 characters, 1 number, and 1 symbol"
                                                isError={Boolean(
                                                    errors.password,
                                                )}
                                                errorMessage={errors.password}
                                                required
                                            />
                                        </FieldGroup>
                                    </FieldSet>

                                    {/* Generic error message */}
                                    {flash.formError === "error" && (
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
                                            : "Submit"}{" "}
                                    </Button>

                                    <div className="text-muted-foreground mt-4 flex justify-center gap-1">
                                        <span>Already have an account?</span>
                                        <InertiaLink href="/sign-in">
                                            <span className="text-foreground">
                                                sign in
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
