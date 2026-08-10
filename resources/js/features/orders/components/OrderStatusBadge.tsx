import type {
    OrderStatus,
    PaymentStatus,
} from "@/features/orders/types/order.types";

const classes: Record<OrderStatus | PaymentStatus, string> = {
    pending: "bg-amber-50 text-amber-700",
    confirmed: "bg-blue-50 text-blue-700",
    processing: "bg-violet-50 text-violet-700",
    shipped: "bg-cyan-50 text-cyan-700",
    delivered: "bg-emerald-50 text-emerald-700",
    cancelled: "bg-red-50 text-red-700",
    paid: "bg-emerald-50 text-emerald-700",
    failed: "bg-red-50 text-red-700",
    refunded: "bg-slate-100 text-slate-700",
};

export function OrderStatusBadge({
    status,
}: {
    status: OrderStatus | PaymentStatus;
}) {
    return (
        <span
            className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize ${classes[status]}`}
        >
            {status}
        </span>
    );
}
