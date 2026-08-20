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
import { InputField } from "@/components/form/InputField";
import { FormError } from "@/components/form/FormError";
import { AppPageProps } from "@/types/SessionTypes";

export default function SignUp() {
    const { flash } = usePage<AppPageProps>().props;

    // Render
    return (
        <>
            <Head title="Sign Up" />
            
            <AuthLayout>
                <Card className="w-120 p-8">
                    <InertiaForm action="/sign-up" method="post">
                        {({ errors }) => {
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
                                                error={Boolean(errors.name)}
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
                                                error={Boolean(errors.email)}
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
                                                error={Boolean(errors.password)}
                                                errorMessage={errors.password}
                                                required
                                            />
                                        </FieldGroup>
                                    </FieldSet>

                                    {/* Generic error message */}
                                    {flash.error && (
                                        <FormError
                                            title="Submission failed"
                                            message={flash.error}
                                        />
                                    )}

                                    {/* Submit */}
                                    <Button
                                        type="submit"
                                        className="bg-foreground text-background hover:bg-foreground/90 mt-8 w-full py-4 hover:shadow-md active:scale-[0.98]"
                                    >
                                        Submit
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
