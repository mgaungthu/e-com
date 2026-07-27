import type { InventoryStockStatus } from "@/features/inventory/types/inventory.types";

type InventoryStatusBadgeProps = {
    status: InventoryStockStatus;
};

const statusConfig: Record<
    InventoryStockStatus,
    {
        label: string;
        className: string;
        dotClassName: string;
    }
> = {
    in_stock: {
        label: "In stock",
        className: "bg-emerald-50 text-emerald-700",
        dotClassName: "bg-emerald-500",
    },
    low_stock: {
        label: "Low stock",
        className: "bg-amber-50 text-amber-700",
        dotClassName: "bg-amber-500",
    },
    out_of_stock: {
        label: "Out of stock",
        className: "bg-red-50 text-red-700",
        dotClassName: "bg-red-500",
    },
};

export function InventoryStatusBadge({
    status,
}: InventoryStatusBadgeProps) {
    const config = statusConfig[status];

    return (
        <span
            className={[
                "inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold",
                config.className,
            ].join(" ")}
        >
            <span
                className={[
                    "h-1.5 w-1.5 rounded-full",
                    config.dotClassName,
                ].join(" ")}
                aria-hidden="true"
            />
            {config.label}
        </span>
    );
}
