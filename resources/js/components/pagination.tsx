import {
    Pagination as ShadcnPagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationLink,
    PaginationNext,
    PaginationPrevious,
} from "@/components/ui/pagination";
import { PaginatedProps } from "@/types/inertia-props";
import {
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
} from "lucide-react";

type PaginationsProps<T> = PaginatedProps<T>;

export function Pagination({
    path,
    current_page,
    last_page,
    prev_page_url,
    next_page_url,
    first_page_url,
    last_page_url,
}: PaginationsProps<unknown>) {
    const pageCount = Math.min(5, last_page);

    const startPage = Math.min(
        Math.max(current_page - 2, 1),
        last_page - pageCount + 1,
    );

    const numberedPages = Array.from(
        { length: pageCount },
        (_, index) => startPage + index,
    );

    const isFirstPage = current_page === 1;
    const isLastPage = current_page === last_page;

    return (
        <ShadcnPagination>
            <PaginationContent>
                {/* First */}
                <PaginationItem>
                    <PaginationLink
                        href={!isFirstPage ? first_page_url : undefined}
                        aria-disabled={isFirstPage}
                        className={
                            isFirstPage ? "pointer-events-none opacity-50" : ""
                        }
                    >
                        <ChevronsLeft />
                    </PaginationLink>
                </PaginationItem>

                {/* Previous */}
                <PaginationItem>
                    <PaginationLink
                        href={prev_page_url ?? undefined}
                        aria-disabled={!prev_page_url}
                        className={
                            !prev_page_url
                                ? "pointer-events-none opacity-50"
                                : ""
                        }
                    >
                        <ChevronLeft />
                    </PaginationLink>
                </PaginationItem>

                {/* Numbered pages */}
                {numberedPages.map((page) => {
                    return (
                        <PaginationItem key={page}>
                            <PaginationLink
                                href={`${path}?page=${page}`}
                                isActive={page === current_page}
                            >
                                {page}
                            </PaginationLink>
                        </PaginationItem>
                    );
                })}

                {/* Next */}
                <PaginationItem>
                    <PaginationLink
                        href={next_page_url ?? undefined}
                        aria-disabled={!next_page_url}
                        className={
                            !next_page_url
                                ? "pointer-events-none opacity-50"
                                : ""
                        }
                    >
                        <ChevronRight />
                    </PaginationLink>
                </PaginationItem>

                {/* Last */}
                <PaginationItem>
                    <PaginationLink
                        href={!isLastPage ? last_page_url : undefined}
                        aria-disabled={isLastPage}
                        className={
                            isLastPage ? "pointer-events-none opacity-50" : ""
                        }
                    >
                        <ChevronsRight />
                    </PaginationLink>
                </PaginationItem>
            </PaginationContent>
        </ShadcnPagination>
    );
}
