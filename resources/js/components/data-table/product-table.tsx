import { MoreHorizontalIcon } from "lucide-react";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
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
import { Product } from "@/types/models";
import { useState } from "react";
import { useForm, Link as InertiaLink } from "@inertiajs/react";
import { toast } from "sonner";

interface ProductsTableProps {
    products: Product[];
}

export function ProductsTable({ products }: ProductsTableProps) {
    const [openDeleteDialog, setOpenDeleteDialog] = useState(false);
    const [selectedProductId, setSelectedProductId] = useState<number | null>(
        null,
    );
    const { delete: deleteProduct } = useForm();
    const toastPosition = { position: "top-right" as const };

    // Delete confirmation dialog
    function triggerDeleteDialog(id: number) {
        setSelectedProductId(id);
        setOpenDeleteDialog(true);
    }

    // Submit handler for article deletion
    function deleteHandler() {
        if (!selectedProductId) return;

        const toastId = "delete-product";

        setOpenDeleteDialog(false);

        deleteProduct(`/admin/products/${selectedProductId}`, {
            onStart: () => {
                toast.loading("Deleting product...", {
                    id: toastId,
                    ...toastPosition,
                });
            },
            onSuccess: () => {
                toast.success("Product deleted successfully.", {
                    id: toastId,
                    ...toastPosition,
                });
            },
            onError: () => {
                toast.error("Failed to delete product.", {
                    id: toastId,
                    ...toastPosition,
                });
            },
        });

        setSelectedProductId(null);
    }

    return (
        <>
            <Table>
                <TableHeader>
                    <TableRow className="bg-secondary">
                        <TableHead className="font-bold">Name</TableHead>
                        <TableHead className="font-bold">Stock</TableHead>
                        <TableHead className="font-bold">Displayed</TableHead>
                        <TableHead className="text-right font-bold">
                            Actions
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    {products.map((product) => (
                        <TableRow key={product.id}>
                            <TableCell>{product.name}</TableCell>
                            <TableCell>{product.stock}</TableCell>
                            <TableCell>
                                {product.in_display ? "Yes" : "No"}
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
                                            href={`/admin/products/${product.id}/edit`}
                                        >
                                            <DropdownMenuItem>
                                                Edit
                                            </DropdownMenuItem>
                                        </InertiaLink>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            variant="destructive"
                                            onClick={() =>
                                                triggerDeleteDialog(product.id)
                                            }
                                        >
                                            Delete
                                        </DropdownMenuItem>
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
                            Confirm to delete this product?
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
