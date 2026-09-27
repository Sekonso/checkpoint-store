import { MoreHorizontalIcon } from "lucide-react";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { formatCurrency, formatDate } from "@/lib/utils";
import { Transaction } from "@/types/models";
import { useState } from "react";
import { useForm, Link as InertiaLink } from "@inertiajs/react";
import { toast } from "sonner";

interface TransactionsTableProps {
    transactions: Transaction[];
}

const statusBadgeVariant = {
    pending: "outline",
    paid: "secondary",
    cancelled: "destructive",
} as const;

export function TransactionsTable({ transactions }: TransactionsTableProps) {
    const [openDeleteDialog, setOpenDeleteDialog] = useState(false);
    const [selectedTransactionId, setSelectedTransactionId] = useState<
        number | null
    >(null);
    const { delete: deleteTransaction } = useForm();
    const toastPosition = { position: "top-right" as const };

    // Delete confirmation dialog
    function triggerDeleteDialog(id: number) {
        setSelectedTransactionId(id);
        setOpenDeleteDialog(true);
    }

    // Submit handler for transaction deletion
    function deleteHandler() {
        if (!selectedTransactionId) return;

        const toastId = "delete-transaction";

        setOpenDeleteDialog(false);

        deleteTransaction(`/admin/transactions/${selectedTransactionId}`, {
            onStart: () => {
                toast.loading("Deleting transaction...", {
                    id: toastId,
                    ...toastPosition,
                });
            },
            onSuccess: () => {
                toast.success("Transaction deleted successfully.", {
                    id: toastId,
                    ...toastPosition,
                });
            },
            onError: () => {
                toast.error("Failed to delete transaction.", {
                    id: toastId,
                    ...toastPosition,
                });
            },
        });

        setSelectedTransactionId(null);
    }

    return (
        <>
            <Table>
                <TableHeader>
                    <TableRow className="bg-secondary">
                        <TableHead className="font-bold">
                            Invoice Number
                        </TableHead>
                        <TableHead className="font-bold">Date</TableHead>
                        <TableHead className="font-bold">Customer</TableHead>
                        <TableHead className="font-bold">Total</TableHead>
                        <TableHead className="font-bold">Status</TableHead>
                        <TableHead className="text-right font-bold">
                            Actions
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    {transactions.map((transaction) => (
                        <TableRow key={transaction.id}>
                            <TableCell className="font-medium">
                                {transaction.invoice_number}
                            </TableCell>
                            <TableCell>
                                {formatDate(transaction.created_at)}
                            </TableCell>
                            <TableCell>{transaction.name}</TableCell>
                            <TableCell>
                                {formatCurrency(transaction.total_amount)}
                            </TableCell>
                            <TableCell>
                                <Badge
                                    variant={
                                        statusBadgeVariant[transaction.status]
                                    }
                                    className="capitalize"
                                >
                                    {transaction.status}
                                </Badge>
                            </TableCell>

                            {/* Actions */}
                            <TableCell className="text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger
                                        render={
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                className="size-8"
                                            >
                                                <MoreHorizontalIcon />
                                                <span className="sr-only">
                                                    Open menu
                                                </span>
                                            </Button>
                                        }
                                    />
                                    <DropdownMenuContent align="end">
                                        <InertiaLink
                                            href={`/admin/transactions/${transaction.id}/edit`}
                                        >
                                            <DropdownMenuItem>
                                                Edit
                                            </DropdownMenuItem>
                                        </InertiaLink>
                                        {transaction.status === "cancelled" && (
                                            <>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem
                                                    variant="destructive"
                                                    onClick={() =>
                                                        triggerDeleteDialog(
                                                            transaction.id,
                                                        )
                                                    }
                                                >
                                                    Delete
                                                </DropdownMenuItem>
                                            </>
                                        )}
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>

            {/* Delete confirmation dialog */}
            <Dialog open={openDeleteDialog} onOpenChange={setOpenDeleteDialog}>
                <DialogContent showCloseButton={false}>
                    <DialogHeader className="mb-4">
                        <DialogTitle className="text-center text-lg font-bold">
                            Confirm to delete this transaction?
                        </DialogTitle>
                    </DialogHeader>

                    <div className="grid grid-cols-2 gap-4">
                        <Button
                            type="submit"
                            onClick={deleteHandler}
                            className="bg-destructive"
                        >
                            Yes
                        </Button>
                        <Button
                            onClick={() => setOpenDeleteDialog(false)}
                            className="bg-accent text-accent-foreground"
                        >
                            Cancel
                        </Button>
                    </div>
                </DialogContent>
            </Dialog>
        </>
    );
}
