import MainLayout from "@/layouts/MainLayout";
import { InputField } from "@/components/form/input-field";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { AuthProps } from "@/types/inertia-props";
import { Form as InertiaForm, Head, usePage } from "@inertiajs/react";
import { UserRound } from "lucide-react";
import { TextAreaField } from "@/components/form/textarea-field";

export default function UserProfilePage() {
    const { auth } = usePage<AuthProps>().props;
    const user = auth.user;

    if (!user) {
        return null;
    }

    return (
        <>
            <Head title="My Profile" />

            <MainLayout>
                <div className="wrapper grid gap-6 py-10 lg:grid-cols-[minmax(16rem,0.8fr)_minmax(0,1.4fr)]">
                    {/* Profile Card */}
                    <Card className="h-fit">
                        <CardContent className="flex flex-col items-center gap-4 pt-6 text-center">
                            <div className="bg-muted flex size-24 items-center justify-center rounded-full">
                                <UserRound
                                    className="text-muted-foreground size-12"
                                    aria-hidden="true"
                                />
                            </div>
                            <div>
                                <h1 className="font-heading text-2xl font-bold">
                                    {user.name}
                                </h1>
                                <p className="text-muted-foreground text-sm">
                                    {user.email}
                                </p>
                            </div>
                            <Badge variant="secondary" className="capitalize">
                                {user.role}
                            </Badge>
                        </CardContent>
                    </Card>

                    {/* Profile Field */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-xl font-semibold mb-2">Edit profile</CardTitle>
                        </CardHeader>

                        <CardContent>
                            <InertiaForm action="/user" method="put">
                                {({ errors, processing }) => (
                                    <div className="flex flex-col gap-6">
                                        <div className="grid gap-6 sm:grid-cols-2">
                                            {/* Name */}
                                            <InputField
                                                id="name"
                                                name="name"
                                                label="Name"
                                                defaultValue={user.name}
                                                autoComplete="name"
                                                placeholder="John Doe"
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
                                                defaultValue={user.email}
                                                autoComplete="email"
                                                placeholder="email@example.com"
                                                isError={Boolean(errors.email)}
                                                errorMessage={errors.email}
                                                required
                                            />

                                            {/* Phone */}
                                            <InputField
                                                id="phone"
                                                name="phone"
                                                label="Phone"
                                                defaultValue={user.phone ?? ""}
                                                autoComplete="phone"
                                                placeholder="e.g. 081234567890"
                                                isError={Boolean(errors.phone)}
                                                errorMessage={errors.phone}
                                            />

                                            {/* Password */}
                                            <InputField
                                                id="password"
                                                name="password"
                                                label="New password"
                                                type="password"
                                                autoComplete="new-password"
                                                placeholder="Your new password"
                                                isError={Boolean(
                                                    errors.password,
                                                )}
                                                errorMessage={errors.password}
                                            />
                                        </div>

                                        {/* Address */}
                                        <TextAreaField
                                            id="address"
                                            name="address"
                                            label="Address"
                                            defaultValue={user.address ?? ""}
                                            autoComplete="street-address"
                                            placeholder="Your address (As delivary destination)"
                                            isError={Boolean(errors.address)}
                                            errorMessage={errors.address}
                                        />

                                        {/* Submit */}
                                        <div className="flex justify-end">
                                            <Button
                                                type="submit"
                                                disabled={processing}
                                            >
                                                {processing
                                                    ? "Saving..."
                                                    : "Save changes"}
                                            </Button>
                                        </div>
                                    </div>
                                )}
                            </InertiaForm>
                        </CardContent>
                    </Card>
                </div>
            </MainLayout>
        </>
    );
}
