import { cn, SHIPPING_STATUS_LABELS, SHIPPING_STATUS_ORDER } from "@/lib/utils";
import { ShippingStatus } from "@/types/models";
import {
    CheckIcon,
    PackageIcon,
    PackageCheckIcon,
    TruckIcon,
} from "lucide-react";

const stepIcons = {
    idle: PackageIcon,
    packaging: PackageCheckIcon,
    in_transit: TruckIcon,
    delivered: CheckIcon,
} as const;

interface ShippingStatusTrackerProps {
    status: ShippingStatus;
}

export function ShippingStatusTracker({ status }: ShippingStatusTrackerProps) {
    const currentIndex = SHIPPING_STATUS_ORDER.indexOf(status);

    return (
        <ol className="flex flex-col gap-0">
            {SHIPPING_STATUS_ORDER.map((step, index) => {
                const StepIcon = stepIcons[step];
                const isDone = index < currentIndex;
                const isCurrent = index === currentIndex;
                const isLast = index === SHIPPING_STATUS_ORDER.length - 1;

                return (
                    <li key={step} className="flex gap-3">
                        {/* Marker and connector */}
                        <div className="flex flex-col items-center">
                            <span
                                className={cn(
                                    "flex size-8 shrink-0 items-center justify-center rounded-full border transition-colors",
                                    isDone &&
                                        "border-emerald-500 bg-emerald-500 text-white",
                                    isCurrent &&
                                        "border-primary bg-primary text-primary-foreground",
                                    !isDone &&
                                        !isCurrent &&
                                        "border-border bg-muted text-muted-foreground",
                                )}
                            >
                                <StepIcon className="size-4" />
                            </span>
                            {!isLast && (
                                <span
                                    className={cn(
                                        "w-px flex-1",
                                        index < currentIndex
                                            ? "bg-emerald-500"
                                            : "bg-border",
                                    )}
                                />
                            )}
                        </div>

                        {/* Label */}
                        <div className={cn("pb-6", isLast && "pb-0")}>
                            <p
                                className={cn(
                                    "text-sm font-semibold",
                                    !isDone &&
                                        !isCurrent &&
                                        "text-muted-foreground",
                                )}
                            >
                                {SHIPPING_STATUS_LABELS[step]}
                            </p>
                            {isCurrent && (
                                <p className="text-muted-foreground text-xs">
                                    Current status
                                </p>
                            )}
                        </div>
                    </li>
                );
            })}
        </ol>
    );
}
