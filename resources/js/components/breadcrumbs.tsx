import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/components/ui/breadcrumb";
import React from "react";

interface BreadcrumbsProps {
    items: {
        name: string;
        href: string;
    }[];
}

export function Breadcrumbs({ items }: BreadcrumbsProps) {
    return (
        <Breadcrumb>
            <BreadcrumbList>
                {items.map((item, idx) => {
                    const isLast = idx === items.length - 1;

                    return isLast ? (
                        <BreadcrumbItem key={idx}>
                            <BreadcrumbPage>{item.name}</BreadcrumbPage>
                        </BreadcrumbItem>
                    ) : (
                        <React.Fragment key={idx}>
                            <BreadcrumbItem key={item.name}>
                                <BreadcrumbLink href={item.href}>
                                    {item.name}
                                </BreadcrumbLink>
                                <BreadcrumbSeparator />
                            </BreadcrumbItem>
                        </React.Fragment>
                    );
                })}
            </BreadcrumbList>
        </Breadcrumb>
    );
}
