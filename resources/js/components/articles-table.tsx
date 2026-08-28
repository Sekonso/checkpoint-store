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
import { Article } from "@/types/models";
import { useState } from "react";
import { useForm, Link as InertiaLink } from "@inertiajs/react";
import { toast } from "sonner";

interface ArticlesTableProps {
    articles: Article[];
}

export function ArticlesTable({ articles }: ArticlesTableProps) {
    const [openDeleteDialog, setOpenDeleteDialog] = useState(false);
    const [selectedArticleId, setSelectedArticleId] = useState<number | null>(
        null,
    );
    const { delete: deleteArticle, patch: patchArticle } = useForm();
    const toastPosition = { position: "top-right" as const };

    // Delete confirmation dialog
    function triggerDeleteDialog(id: number) {
        setSelectedArticleId(id);
        setOpenDeleteDialog(true);
    }

    // Submit handler for article deletion
    function deleteHandler() {
        if (!selectedArticleId) return;

        const toastId = "delete-article";

        setOpenDeleteDialog(false);

        deleteArticle(`/admin/articles/${selectedArticleId}`, {
            onStart: () => {
                toast.loading("Deleting article...", {
                    id: toastId,
                    ...toastPosition,
                });
            },
            onSuccess: () => {
                toast.success("Article deleted successfully.", {
                    id: toastId,
                    ...toastPosition,
                });
            },
            onError: () => {
                toast.error("Failed to delete article.", {
                    id: toastId,
                    ...toastPosition,
                });
            },
        });

        setSelectedArticleId(null);
    }

    function archiveHandler(id: number) {
        const toastId = "archive-article";

        patchArticle(`/admin/articles/${id}/archive`, {
            onStart: () => {
                toast.loading("Archiving article...", {
                    id: toastId,
                    ...toastPosition,
                });
            },
            onSuccess: () => {
                toast.success("Article archived successfully.", {
                    id: toastId,
                    ...toastPosition,
                });
            },
            onError: () => {
                toast.error("Failed to archive article.", {
                    id: toastId,
                    ...toastPosition,
                });
            },
        });
    }

    return (
        <>
            <Table>
                <TableHeader>
                    <TableRow className="bg-secondary">
                        <TableHead className="font-bold">Title</TableHead>
                        <TableHead className="font-bold">Author</TableHead>
                        <TableHead className="font-bold">Status</TableHead>
                        <TableHead className="text-right font-bold">
                            Actions
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    {articles.map((article) => (
                        <TableRow key={article.id}>
                            <TableCell>{article.title}</TableCell>
                            <TableCell>{article.user.name}</TableCell>
                            <TableCell>{article.status}</TableCell>

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
                                            href={`/admin/articles/${article.id}/edit`}
                                        >
                                            <DropdownMenuItem>
                                                Edit
                                            </DropdownMenuItem>
                                        </InertiaLink>
                                        {article.status !== "archived" && (
                                            <DropdownMenuItem
                                                onClick={() => {
                                                    archiveHandler(article.id);
                                                }}
                                            >
                                                Archive
                                            </DropdownMenuItem>
                                        )}
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            variant="destructive"
                                            onClick={() =>
                                                triggerDeleteDialog(article.id)
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
                            Confirm to delete this article?
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
