import { useEffect, useRef, useState } from "react";
import MainLayout from "@/layouts/MainLayout";
import { CompletedPayment, Transaction } from "@/types/models";
import { formatCurrency } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import {
    Head,
    Link as InertiaLink,
    router,
    useForm,
    usePage,
} from "@inertiajs/react";
import { PageProps } from "@inertiajs/core";
import { FlashProps } from "@/types/inertia-props";
import {
    CheckCircle2,
    ArrowLeft,
    Clock3,
    CreditCard,
    XCircle,
    RefreshCw,
    Wallet,
} from "lucide-react";

interface PaymentInfo {
    snap_token: string;
    client_key: string;
    is_production: boolean;
}

interface TransactionInvoicePageProps extends PageProps, FlashProps {
    transaction: Transaction;
    payment: PaymentInfo | null;
    completedPayment: CompletedPayment | null;
}

export default function TransactionInvoice({
    transaction,
    payment,
    completedPayment,
}: TransactionInvoicePageProps) {
    const { flash } = usePage<TransactionInvoicePageProps>().props;
    const { post: cancelTransaction, processing: isCancelling } = useForm();
    const { post: initiatePayment, processing: isChoosing } = useForm();
    const { post: resetPayment, processing: isResetting } = useForm();
    const { post: syncPayment, processing: isSyncing } = useForm();
    const [openCancelDialog, setOpenCancelDialog] = useState(false);
    const [openResetDialog, setOpenResetDialog] = useState(false);
    const [snapReady, setSnapReady] = useState(false);
    const autoOpened = useRef(false);

    const isPending =
        transaction.status === "pending" && !transaction.is_complete;
    const isPaid = transaction.status === "paid";
    const isExpired =
        isPending &&
        transaction.expires_at !== null &&
        new Date(transaction.expires_at).getTime() < Date.now();

    const formattedDate = new Date(transaction.created_at).toLocaleDateString(
        "en-US",
        {
            year: "numeric",
            month: "long",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        },
    );

    const formattedExpiryDate = transaction.expires_at
        ? new Date(transaction.expires_at).toLocaleDateString("en-US", {
              year: "numeric",
              month: "long",
              day: "numeric",
              hour: "2-digit",
              minute: "2-digit",
          })
        : null;

    const formattedPaidDate = completedPayment?.paid_at
        ? new Date(completedPayment.paid_at).toLocaleDateString("en-US", {
              year: "numeric",
              month: "long",
              day: "numeric",
              hour: "2-digit",
              minute: "2-digit",
          })
        : "-";

    useEffect(() => {
        if (!payment || window.snap) {
            setSnapReady(Boolean(window.snap && payment));
            return;
        }

        const script = document.createElement("script");
        const snapBaseUrl = payment.is_production
            ? "https://app.midtrans.com"
            : "https://app.sandbox.midtrans.com";

        script.src = `${snapBaseUrl}/snap/snap.js`;
        script.dataset.clientKey = payment.client_key;
        script.async = true;
        script.onload = () => setSnapReady(true);
        document.body.appendChild(script);

        return () => {
            script.remove();
        };
    }, [payment]);

    function openSnap() {
        if (!payment || !window.snap) {
            return;
        }

        window.snap.pay(payment.snap_token, {
            onSuccess: () => router.reload(),
            onPending: () => router.reload(),
            onError: () => router.reload(),
            onClose: () => router.reload(),
        });
    }

    useEffect(() => {
        if (flash?.openPayment && payment && snapReady && !autoOpened.current) {
            autoOpened.current = true;
            openSnap();
        }
    }, [flash?.openPayment, payment, snapReady]);

    function handleCancel() {
        cancelTransaction(`/transactions/${transaction.invoice_number}/cancel`);
    }

    function handlePaymentInitiation() {
        initiatePayment(`/transactions/${transaction.invoice_number}/payments`);
    }

    function handleReset() {
        setOpenResetDialog(false);
        resetPayment(
            `/transactions/${transaction.invoice_number}/payments/reset`,
        );
    }

    function handleSync() {
        syncPayment(
            `/transactions/${transaction.invoice_number}/payments/sync`,
        );
    }

    const isBusy = isCancelling || isChoosing || isResetting || isSyncing;

    const StatusIcon = isPaid ? CheckCircle2 : isPending ? Clock3 : XCircle;
    const statusIconColor = isPaid
        ? "text-emerald-500"
        : isPending
          ? "text-amber-500"
          : "text-red-500";
    const statusTextColor = isPaid
        ? "text-emerald-600 dark:text-emerald-400"
        : isPending
          ? "text-amber-600 dark:text-amber-400"
          : "text-red-600 dark:text-red-400";

    return (
        <>
            <Head title={`Purchase Detail — ${transaction.invoice_number}`} />

            <MainLayout>
                <div className="wrapper max-w-3xl py-10">
                    {/* Status header */}
                    <div className="mb-6 flex flex-col items-center text-center">
                        <StatusIcon className={`size-14 ${statusIconColor}`} />
                        <h1 className="font-heading mt-3 text-3xl font-bold">
                            Purchase Detail
                        </h1>
                        <p className="text-muted-foreground mt-1">
                            Invoice Code: {transaction.invoice_number}
                        </p>
                    </div>

                    <Card className="mb-6">
                        <CardHeader className="border-b pb-4">
                            <div className="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <span className="text-muted-foreground text-xs tracking-wider uppercase">
                                        Transaction Date
                                    </span>
                                    <p className="text-sm font-medium">
                                        {formattedDate}
                                    </p>
                                </div>
                                {isPending && formattedExpiryDate && (
                                    <div>
                                        <span className="text-muted-foreground text-xs tracking-wider uppercase">
                                            Expires At
                                        </span>
                                        <p className="text-sm font-medium">
                                            {formattedExpiryDate}
                                        </p>
                                    </div>
                                )}
                                <div className="text-right">
                                    <span className="text-muted-foreground text-xs tracking-wider uppercase">
                                        Status
                                    </span>
                                    <p
                                        className={`text-sm font-semibold capitalize ${statusTextColor}`}
                                    >
                                        {transaction.status}
                                    </p>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent className="space-y-6 pt-6">
                            {/* Customer info */}
                            <div className="bg-muted/50 rounded-lg p-4 text-sm">
                                <h3 className="text-foreground mb-2 font-semibold">
                                    Shipping Information
                                </h3>
                                <p className="font-medium">
                                    {transaction.name}
                                </p>
                                <p className="text-muted-foreground">
                                    {transaction.phone || "-"}
                                </p>
                                <p className="text-muted-foreground mt-1">
                                    {transaction.address || "-"}
                                </p>
                            </div>

                            {/* Item list */}
                            <div>
                                <h3 className="mb-3 font-semibold">
                                    Order Details
                                </h3>
                                <div className="divide-y rounded-lg border">
                                    {transaction.items?.map((item) => (
                                        <div
                                            key={item.id}
                                            className="flex items-center justify-between p-4"
                                        >
                                            <div>
                                                <p className="font-medium">
                                                    {item.product_name}
                                                </p>
                                                <p className="text-muted-foreground text-sm">
                                                    {formatCurrency(item.price)}{" "}
                                                    &times; {item.quantity}
                                                </p>
                                            </div>
                                            <span className="font-semibold">
                                                {formatCurrency(item.subtotal)}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* Total summary */}
                            <div className="flex items-center justify-between border-t pt-4 text-lg font-bold">
                                <span>Total Payment</span>
                                <span>
                                    {formatCurrency(transaction.total_amount)}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Payment details */}
                    {isPaid && completedPayment && (
                        <Card className="mb-6">
                            <CardHeader className="border-b pb-4">
                                <CardTitle className="text-lg">
                                    Payment Details
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 pt-6 text-sm">
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">
                                        Payment date
                                    </span>
                                    <span className="font-medium">
                                        {formattedPaidDate}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">
                                        Payment method
                                    </span>
                                    <span className="font-medium capitalize">
                                        {(
                                            completedPayment.method ?? "-"
                                        ).replace(/_/g, " ")}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">
                                        Amount paid
                                    </span>
                                    <span className="font-medium">
                                        {formatCurrency(
                                            completedPayment.amount,
                                        )}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-muted-foreground">
                                        Status
                                    </span>
                                    <span className="font-semibold text-emerald-600 capitalize dark:text-emerald-400">
                                        {completedPayment.status}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Actions */}
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        {/* Payment-related actions */}
                        <div className="flex flex-col gap-3 sm:flex-row">
                            {isPending && !payment && !isExpired && (
                                <Button
                                    className="gap-2"
                                    onClick={handlePaymentInitiation}
                                    disabled={isBusy}
                                >
                                    <Wallet className="size-4" />
                                    {isChoosing
                                        ? "Initiating..."
                                        : "Initiate payment"}
                                </Button>
                            )}
                            {isPending && payment && snapReady && (
                                <Button
                                    className="gap-2"
                                    onClick={openSnap}
                                    disabled={isBusy}
                                >
                                    <CreditCard className="size-4" />
                                    Pay Now
                                </Button>
                            )}
                            {isPending && payment && (
                                <>
                                    <Button
                                        variant="outline"
                                        className="gap-2"
                                        onClick={() => setOpenResetDialog(true)}
                                        disabled={isBusy}
                                    >
                                        <RefreshCw className="size-4" />
                                        {isResetting
                                            ? "Restarting..."
                                            : "Reset payment"}
                                    </Button>
                                    <Button
                                        variant="outline"
                                        className="gap-2"
                                        onClick={handleSync}
                                        disabled={isBusy}
                                    >
                                        <RefreshCw className="size-4" />
                                        {isSyncing
                                            ? "Checking..."
                                            : "Check status"}
                                    </Button>
                                </>
                            )}
                        </div>

                        {/* Transaction-related actions */}
                        <div className="flex flex-col gap-3 sm:flex-row">
                            {isPending && (
                                <Button
                                    variant="destructive"
                                    className="gap-2"
                                    onClick={() => setOpenCancelDialog(true)}
                                    disabled={isBusy}
                                >
                                    <XCircle className="size-4" />
                                    {isCancelling
                                        ? "Cancelling..."
                                        : "Cancel transaction"}
                                </Button>
                            )}
                            <InertiaLink href="/transactions">
                                <Button variant="outline" className="gap-2">
                                    <ArrowLeft className="size-4" />
                                    Back to history
                                </Button>
                            </InertiaLink>
                        </div>
                    </div>
                </div>
            </MainLayout>

            <Dialog open={openCancelDialog} onOpenChange={setOpenCancelDialog}>
                <DialogContent showCloseButton={false}>
                    <DialogHeader className="mb-4">
                        <DialogTitle className="text-center text-lg font-bold">
                            Confirm to cancel this transaction?
                        </DialogTitle>
                    </DialogHeader>

                    <div className="grid grid-cols-2 gap-4">
                        <Button
                            type="submit"
                            onClick={handleCancel}
                            disabled={isCancelling}
                            className="bg-destructive"
                        >
                            {isCancelling ? "Cancelling..." : "Yes"}
                        </Button>
                        <Button
                            onClick={() => setOpenCancelDialog(false)}
                            className="bg-accent text-accent-foreground"
                        >
                            No
                        </Button>
                    </div>
                </DialogContent>
            </Dialog>

            <Dialog open={openResetDialog} onOpenChange={setOpenResetDialog}>
                <DialogContent showCloseButton={false}>
                    <DialogHeader className="mb-4">
                        <DialogTitle className="text-center text-lg font-bold">
                            Restart this payment session?
                        </DialogTitle>
                    </DialogHeader>

                    <p className="text-muted-foreground mb-4 text-center text-sm">
                        The current payment session will be cancelled and a new
                        one will be created.
                    </p>

                    <div className="grid grid-cols-2 gap-4">
                        <Button
                            type="submit"
                            onClick={handleReset}
                            disabled={isResetting}
                            className="bg-destructive"
                        >
                            {isResetting ? "Restarting..." : "Yes"}
                        </Button>
                        <Button
                            onClick={() => setOpenResetDialog(false)}
                            className="bg-accent text-accent-foreground"
                        >
                            No
                        </Button>
                    </div>
                </DialogContent>
            </Dialog>
        </>
    );
}
