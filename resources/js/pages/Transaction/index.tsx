import MainLayout from "@/layouts/MainLayout";
import { formatCurrency } from "@/lib/utils";
import { Transaction } from "@/types/models";
import { Head, Link as InertiaLink, usePage } from "@inertiajs/react";
import { PageProps } from "@inertiajs/core";
import { ArrowRight, CheckCircle2, Clock3, ReceiptText } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

interface TransactionPageProps extends PageProps {
    transactions: {
        pending?: Transaction[];
        completed?: Transaction[];
    };
}

function formatDate(date: string) {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
}

function TransactionList({ transactions }: { transactions: Transaction[] }) {
    return (
        <div className="grid gap-4">
            {transactions.map((transaction) => (
                <Card key={transaction.id}>
                    <CardContent className="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div className="min-w-0">
                            <div className="flex flex-wrap items-center gap-2">
                                <p className="font-heading font-semibold">
                                    #{transaction.invoice_number}
                                </p>
                                <Badge
                                    variant={
                                        transaction.status === "pending"
                                            ? "outline"
                                            : "secondary"
                                    }
                                >
                                    {transaction.status === "pending"
                                        ? "Awaiting payment"
                                        : "Completed"}
                                </Badge>
                            </div>
                            <p className="text-muted-foreground mt-1 text-sm">
                                {formatDate(transaction.created_at)} ·{" "}
                                {transaction.items.length} item
                                {transaction.items.length === 1 ? "" : "s"}
                            </p>
                        </div>

                        <div className="flex items-center justify-between gap-6 sm:justify-end">
                            <p className="font-semibold">
                                {formatCurrency(transaction.total_amount)}
                            </p>
                            <InertiaLink
                                href={`/transactions/${transaction.invoice_number}`}
                                className="text-primary inline-flex items-center gap-1 text-sm font-medium hover:underline"
                            >
                                Detail
                                <ArrowRight className="size-4" />
                            </InertiaLink>
                        </div>
                    </CardContent>
                </Card>
            ))}
        </div>
    );
}

function EmptyState({ message }: { message: string }) {
    return (
        <div className="text-muted-foreground rounded-xl border border-dashed p-8 text-center text-sm">
            {message}
        </div>
    );
}

export default function TransactionIndex() {
    const { transactions } = usePage<TransactionPageProps>().props;
    const pending = transactions.pending ?? [];
    const completed = transactions.completed ?? [];

    return (
        <>
            <Head title="My Transactions" />

            <MainLayout>
                <div className="wrapper py-10">
                    <div className="mb-8 flex items-center gap-3">
                        <ReceiptText className="text-primary size-8" />
                        <div>
                            <h1 className="font-heading text-3xl font-bold">
                                My Transactions
                            </h1>
                            <p className="text-muted-foreground mt-1">
                                View your order history and payment status.
                            </p>
                        </div>
                    </div>

                    <div className="grid gap-8">
                        <section>
                            <CardHeader className="px-0 pb-3">
                                <CardTitle className="flex items-center gap-2 text-xl">
                                    <Clock3 className="size-5 text-amber-500" />
                                    Pending
                                </CardTitle>
                            </CardHeader>
                            {pending.length ? (
                                <TransactionList transactions={pending} />
                            ) : (
                                <EmptyState message="No pending transactions." />
                            )}
                        </section>

                        <section>
                            <CardHeader className="px-0 pb-3">
                                <CardTitle className="flex items-center gap-2 text-xl">
                                    <CheckCircle2 className="size-5 text-emerald-500" />
                                    Completed
                                </CardTitle>
                            </CardHeader>
                            {completed.length ? (
                                <TransactionList transactions={completed} />
                            ) : (
                                <EmptyState message="No completed transactions yet." />
                            )}
                        </section>
                    </div>
                </div>
            </MainLayout>
        </>
    );
}
