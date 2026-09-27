import AdminLayout from "@/layouts/AdminLayout";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
    SelectField,
    SelectFieldOptions,
} from "@/components/form/select-field";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import {
    formatCurrency,
    SHIPPING_STATUS_LABELS,
    SHIPPING_STATUS_ORDER,
} from "@/lib/utils";
import { CompletedPayment, ShippingStatus, Transaction } from "@/types/models";
import { PageProps } from "@inertiajs/core";
import {
    Form as InertiaForm,
    Head,
    Link as InertiaLink,
    usePage,
} from "@inertiajs/react";
import { ArrowLeftIcon } from "lucide-react";
import { useState } from "react";

interface AdminTransactionsEditPageProps extends PageProps {
    transaction: Transaction;
    completedPayment: CompletedPayment | null;
}

const statusBadgeVariant = {
    pending: "outline",
    paid: "secondary",
    cancelled: "destructive",
} as const;

const shippingStatusBadgeVariant = {
    idle: "outline",
    packaging: "secondary",
    in_transit: "default",
    delivered: "default",
} as const;

const shippingStatusOptions: SelectFieldOptions = SHIPPING_STATUS_ORDER.map(
    (status) => ({
        name: SHIPPING_STATUS_LABELS[status],
        value: status,
    }),
);

function formatDateTime(date: string | null) {
    if (!date) return "-";

    return new Date(date).toLocaleString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

function DetailRow({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex items-start justify-between gap-4 text-sm">
            <span className="text-muted-foreground shrink-0">{label}</span>
            <span className="text-right font-medium break-words">{value}</span>
        </div>
    );
}

export default function AdminTransactionsEditPage() {
    const { transaction, completedPayment } =
        usePage<AdminTransactionsEditPageProps>().props;
    const [shippingStatus, setShippingStatus] = useState(
        transaction.shipping_status,
    );

    const isShippingDirty = shippingStatus !== transaction.shipping_status;

    return (
        <>
            <Head title="Edit Transaction" />

            <AdminLayout>
                <header className="mb-4">
                    <h1 className="text-center text-2xl font-bold sm:text-3xl">
                        Edit Transaction
                    </h1>
                    <p className="text-muted-foreground mt-2 text-center text-sm">
                        {transaction.invoice_number}
                    </p>
                </header>

                <div className="mx-auto flex max-w-250 flex-col gap-6">
                    {/* Customer information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Customer</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-3">
                            <DetailRow
                                label="Name"
                                value={
                                    transaction.user?.name ?? transaction.name
                                }
                            />
                            <DetailRow
                                label="Email"
                                value={transaction.user?.email ?? "-"}
                            />
                            <DetailRow
                                label="Phone"
                                value={transaction.phone || "-"}
                            />
                            <DetailRow
                                label="Address"
                                value={transaction.address || "-"}
                            />
                        </CardContent>
                    </Card>

                    {/* Transaction information */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Transaction</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-3">
                            <DetailRow
                                label="Invoice number"
                                value={transaction.invoice_number}
                            />
                            <DetailRow
                                label="Transaction date"
                                value={formatDateTime(transaction.created_at)}
                            />
                            <div className="flex items-start justify-between gap-4 text-sm">
                                <span className="text-muted-foreground shrink-0">
                                    Status
                                </span>
                                <Badge
                                    variant={
                                        statusBadgeVariant[transaction.status]
                                    }
                                    className="capitalize"
                                >
                                    {transaction.status}
                                </Badge>
                            </div>
                            <DetailRow
                                label="Total"
                                value={formatCurrency(transaction.total_amount)}
                            />
                            <DetailRow
                                label="Expires at"
                                value={formatDateTime(transaction.expires_at)}
                            />
                            <DetailRow
                                label="Paid at"
                                value={formatDateTime(transaction.paid_at)}
                            />
                        </CardContent>
                    </Card>

                    {/* Ordered items */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Order Details</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-secondary">
                                        <TableHead className="font-bold">
                                            Product
                                        </TableHead>
                                        <TableHead className="font-bold">
                                            Price
                                        </TableHead>
                                        <TableHead className="font-bold">
                                            Qty
                                        </TableHead>
                                        <TableHead className="text-right font-bold">
                                            Subtotal
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {transaction.items.map((item) => (
                                        <TableRow key={item.id}>
                                            <TableCell>
                                                {item.product_name}
                                            </TableCell>
                                            <TableCell>
                                                {formatCurrency(item.price)}
                                            </TableCell>
                                            <TableCell>
                                                {item.quantity}
                                            </TableCell>
                                            <TableCell className="text-right font-medium">
                                                {formatCurrency(item.subtotal)}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>

                    {/* Payment information, only once settled */}
                    {transaction.status === "paid" && (
                        <Card>
                            <CardHeader>
                                <CardTitle>Payment</CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-3">
                                <DetailRow
                                    label="Method"
                                    value={
                                        completedPayment?.method?.replace(
                                            /_/g,
                                            " ",
                                        ) ?? "-"
                                    }
                                />
                                <DetailRow
                                    label="Amount paid"
                                    value={
                                        completedPayment
                                            ? formatCurrency(
                                                  completedPayment.amount,
                                              )
                                            : "-"
                                    }
                                />
                                <DetailRow
                                    label="Paid at"
                                    value={formatDateTime(
                                        completedPayment?.paid_at ?? null,
                                    )}
                                />
                                <DetailRow
                                    label="Payment status"
                                    value={completedPayment?.status ?? "-"}
                                />
                            </CardContent>
                        </Card>
                    )}

                    {/* Shipping information, only once the payment settled */}
                    {transaction.status === "paid" && (
                        <InertiaForm
                            action={`/admin/transactions/${transaction.id}/shipping`}
                            method="patch"
                        >
                            {({ errors, processing }) => (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Shipping</CardTitle>
                                    </CardHeader>
                                    <CardContent className="flex flex-col gap-4">
                                        <div className="flex items-start justify-between gap-4 text-sm">
                                            <span className="text-muted-foreground shrink-0">
                                                Current status
                                            </span>
                                            <Badge
                                                variant={
                                                    shippingStatusBadgeVariant[
                                                        transaction
                                                            .shipping_status
                                                    ]
                                                }
                                            >
                                                {SHIPPING_STATUS_LABELS[
                                                    transaction
                                                        .shipping_status
                                                ]}
                                            </Badge>
                                        </div>

                                        <SelectField
                                            id="shipping_status"
                                            name="shipping_status"
                                            label="Shipping status"
                                            options={shippingStatusOptions}
                                            defaultValue={
                                                transaction.shipping_status
                                            }
                                            onChange={(event) =>
                                                setShippingStatus(
                                                    event.target
                                                        .value as ShippingStatus,
                                                )
                                            }
                                            required
                                            isError={Boolean(
                                                errors.shipping_status,
                                            )}
                                            errorMessage={
                                                errors.shipping_status
                                            }
                                        />

                                        <div className="flex items-center justify-end gap-4">
                                            <Button
                                                type="submit"
                                                disabled={
                                                    processing ||
                                                    !isShippingDirty
                                                }
                                            >
                                                {processing
                                                    ? "Saving..."
                                                    : "Confirm change"}
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            )}
                        </InertiaForm>
                    )}

                    <InertiaLink href="/admin/transactions">
                        <Button
                            type="button"
                            variant="outline"
                            className="gap-2"
                        >
                            <ArrowLeftIcon className="size-4" />
                            Back to transactions
                        </Button>
                    </InertiaLink>
                </div>
            </AdminLayout>
        </>
    );
}
