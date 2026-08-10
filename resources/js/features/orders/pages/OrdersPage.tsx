import { useMemo, useState } from "react";

import { Eye, Search, ShoppingCart } from "lucide-react";
import { useNavigate } from "react-router-dom";

import PageHeader from "@/components/PageHeader";
import { OrderStatusBadge } from "@/features/orders/components/OrderStatusBadge";
import { useOrders } from "@/features/orders/hooks/useOrders";
import type {
    OrderStatus,
    PaymentStatus,
} from "@/features/orders/types/order.types";
import { formatMmk } from "@/utils/currency";

const orderStatuses: Array<OrderStatus | "all"> = [
    "all", "pending", "confirmed", "processing", "shipped", "delivered", "cancelled",
];
const paymentStatuses: Array<PaymentStatus | "all"> = [
    "all", "pending", "paid", "failed", "refunded",
];

function formatDate(value: string): string {
    return new Intl.DateTimeFormat("en-US", {
        year: "numeric", month: "short", day: "numeric", hour: "numeric", minute: "2-digit",
    }).format(new Date(value));
}

function customerName(customer: { name: string; display_name: string | null } | null) {
    return customer?.display_name || customer?.name || "Guest customer";
}

export default function OrdersPage() {
    const navigate = useNavigate();
    const [page, setPage] = useState(1);
    const [search, setSearch] = useState("");
    const [status, setStatus] = useState<OrderStatus | "all">("all");
    const [paymentStatus, setPaymentStatus] = useState<PaymentStatus | "all">("all");
    const [dateFrom, setDateFrom] = useState("");
    const [dateTo, setDateTo] = useState("");

    const filters = useMemo(
        () => ({
            page,
            per_page: 15,
            search: search.trim() || undefined,
            status,
            payment_status: paymentStatus,
            date_from: dateFrom || undefined,
            date_to: dateTo || undefined,
        }),
        [dateFrom, dateTo, page, paymentStatus, search, status],
    );
    const ordersQuery = useOrders(filters);
    const pagination = ordersQuery.data?.data;
    const orders = pagination?.data ?? [];

    return (
        <div className="space-y-6">
            <PageHeader
                title="Orders"
                description="Track fulfillment, payments, customers, and order totals."
            />

            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <div className="grid gap-3 border-b border-slate-200 p-4 lg:grid-cols-[minmax(240px,1fr)_repeat(4,minmax(150px,auto))]">
                    <div className="relative">
                        <Search size={18} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            type="search"
                            value={search}
                            onChange={(event) => { setSearch(event.target.value); setPage(1); }}
                            placeholder="Search order or customer..."
                            className="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-4 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        />
                    </div>
                    <select
                        value={status}
                        onChange={(event) => { setStatus(event.target.value as OrderStatus | "all"); setPage(1); }}
                        className="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm"
                    >
                        {orderStatuses.map((value) => (
                            <option key={value} value={value}>{value === "all" ? "All order statuses" : value}</option>
                        ))}
                    </select>
                    <select
                        value={paymentStatus}
                        onChange={(event) => { setPaymentStatus(event.target.value as PaymentStatus | "all"); setPage(1); }}
                        className="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm"
                    >
                        {paymentStatuses.map((value) => (
                            <option key={value} value={value}>{value === "all" ? "All payments" : value}</option>
                        ))}
                    </select>
                    <input
                        type="date"
                        value={dateFrom}
                        aria-label="Orders from date"
                        onChange={(event) => { setDateFrom(event.target.value); setPage(1); }}
                        className="rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    />
                    <input
                        type="date"
                        value={dateTo}
                        aria-label="Orders to date"
                        onChange={(event) => { setDateTo(event.target.value); setPage(1); }}
                        className="rounded-lg border border-slate-300 px-3 py-2.5 text-sm"
                    />
                </div>

                {ordersQuery.isLoading ? (
                    <div className="p-12 text-center text-sm text-slate-500">Loading orders...</div>
                ) : ordersQuery.isError ? (
                    <div className="p-12 text-center">
                        <p className="text-sm font-medium text-red-600">Unable to load orders.</p>
                        <button type="button" onClick={() => void ordersQuery.refetch()} className="mt-3 text-sm font-semibold text-blue-600">Try again</button>
                    </div>
                ) : orders.length === 0 ? (
                    <div className="p-12 text-center">
                        <ShoppingCart size={28} className="mx-auto text-slate-400" />
                        <p className="mt-3 text-sm font-semibold text-slate-700">No orders found.</p>
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    {['Order', 'Customer', 'Date', 'Items', 'Total', 'Payment', 'Status', ''].map((label) => (
                                        <th key={label} className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{label}</th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {orders.map((order) => (
                                    <tr key={order.id} className="hover:bg-slate-50">
                                        <td className="px-5 py-4 text-sm font-semibold text-slate-900">{order.order_number}</td>
                                        <td className="px-5 py-4">
                                            <p className="text-sm font-medium text-slate-800">{customerName(order.customer)}</p>
                                            <p className="text-xs text-slate-500">{order.customer?.email ?? "No account"}</p>
                                        </td>
                                        <td className="whitespace-nowrap px-5 py-4 text-sm text-slate-600">{formatDate(order.created_at)}</td>
                                        <td className="px-5 py-4 text-sm text-slate-600">{order.items_count ?? 0}</td>
                                        <td className="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-900">{formatMmk(order.grand_total)}</td>
                                        <td className="px-5 py-4"><OrderStatusBadge status={order.payment_status} /></td>
                                        <td className="px-5 py-4"><OrderStatusBadge status={order.status} /></td>
                                        <td className="px-5 py-4 text-right">
                                            <button type="button" onClick={() => navigate(`/orders/${order.id}`)} className="rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-100" aria-label={`View ${order.order_number}`}>
                                                <Eye size={16} />
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {pagination ? (
                    <div className="flex items-center justify-between border-t border-slate-200 px-5 py-4">
                        <p className="text-sm text-slate-500">Showing {pagination.from ?? 0}–{pagination.to ?? 0} of {pagination.total}</p>
                        <div className="flex gap-2">
                            <button type="button" disabled={pagination.current_page <= 1} onClick={() => setPage((value) => Math.max(1, value - 1))} className="rounded-lg border px-3 py-2 text-sm disabled:opacity-40">Previous</button>
                            <button type="button" disabled={pagination.current_page >= pagination.last_page} onClick={() => setPage((value) => value + 1)} className="rounded-lg border px-3 py-2 text-sm disabled:opacity-40">Next</button>
                        </div>
                    </div>
                ) : null}
            </div>
        </div>
    );
}
