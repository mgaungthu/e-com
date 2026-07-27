import type { CustomerStatus } from "../types/customer.types";

type CustomerStatusBadgeProps = {
    status: CustomerStatus;
};

const statusClassNames: Record<
    CustomerStatus,
    string
> = {
    active:
        "border-emerald-200 bg-emerald-50 text-emerald-700",
    inactive:
        "border-slate-200 bg-slate-100 text-slate-600",
    blocked:
        "border-red-200 bg-red-50 text-red-700",
    pending:
        "border-amber-200 bg-amber-50 text-amber-700",
};

export function CustomerStatusBadge({
    status,
}: CustomerStatusBadgeProps) {
    return (
        <span
            className={[
                "inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold capitalize",
                statusClassNames[status],
            ].join(" ")}
        >
            {status}
        </span>
    );
}