import { Boxes, Package, ShoppingBag, Users, WalletCards } from "lucide-react";
import { useNavigate } from "react-router-dom";

import { useDashboard } from "@/features/dashboard/hooks/useDashboard";
import { formatMmk } from "@/utils/currency";

export default function DashboardPage() {
    const navigate = useNavigate();
    const query = useDashboard();
    if (query.isLoading) return <div className="p-10 text-center text-slate-500">Loading analytics...</div>;
    if (query.isError || !query.data) return <div className="p-10 text-center text-red-600">Unable to load dashboard analytics.</div>;
    const { summary, order_statuses: statuses, sales_trend: trend, recent_orders: recent, top_products: top } = query.data.data;
    const maxRevenue = Math.max(...trend.map((item) => Number(item.revenue)), 1);
    const cards = [
        ["Total revenue", formatMmk(summary.total_revenue), WalletCards],
        ["Total orders", summary.total_orders, ShoppingBag],
        ["Customers", summary.total_customers, Users],
        ["Products", summary.total_products, Package],
        ["Low stock", summary.low_stock_products, Boxes],
    ] as const;

    return (
        <div className="space-y-6">
            <div><h1 className="text-2xl font-bold text-slate-900">Dashboard</h1><p className="mt-1 text-sm text-slate-500">Sales, orders, customers, and inventory at a glance.</p></div>
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">{cards.map(([label, value, Icon]) => <div key={label} className="rounded-xl border border-slate-200 bg-white p-5"><div className="flex items-center justify-between"><p className="text-sm text-slate-500">{label}</p><Icon size={19} className="text-blue-600" /></div><p className="mt-3 text-2xl font-bold text-slate-900">{value}</p></div>)}</div>
            <div className="grid gap-6 xl:grid-cols-2">
                <section className="rounded-xl border border-slate-200 bg-white p-5"><h2 className="font-bold text-slate-900">7-day revenue</h2><div className="mt-6 flex h-56 items-end gap-3">{trend.map((item) => <div key={item.date} className="flex flex-1 flex-col items-center justify-end gap-2"><span className="text-[10px] text-slate-500">{Number(item.revenue) ? formatMmk(item.revenue) : ""}</span><div className="w-full rounded-t bg-blue-500" style={{ height: `${Math.max((Number(item.revenue) / maxRevenue) * 160, 4)}px` }} /><span className="text-xs text-slate-500">{new Date(item.date).toLocaleDateString("en-US", { weekday: "short" })}</span></div>)}</div></section>
                <section className="rounded-xl border border-slate-200 bg-white p-5"><h2 className="font-bold text-slate-900">Order pipeline</h2><div className="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">{Object.entries(statuses).map(([status, count]) => <div key={status} className="rounded-lg bg-slate-50 p-4"><p className="text-xs capitalize text-slate-500">{status}</p><p className="mt-1 text-xl font-bold text-slate-900">{count}</p></div>)}</div></section>
            </div>
            <div className="grid gap-6 xl:grid-cols-2">
                <section className="rounded-xl border border-slate-200 bg-white p-5"><h2 className="font-bold text-slate-900">Recent orders</h2><div className="mt-3 divide-y">{recent.length ? recent.map((order) => <button key={order.id} type="button" onClick={() => navigate(`/orders/${order.id}`)} className="flex w-full justify-between py-3 text-left"><div><p className="text-sm font-semibold">{order.order_number}</p><p className="text-xs text-slate-500">{order.customer?.display_name || order.customer?.name || "Guest"}</p></div><div className="text-right"><p className="text-sm font-bold">{formatMmk(order.grand_total)}</p><p className="text-xs capitalize text-slate-500">{order.status}</p></div></button>) : <p className="py-6 text-sm text-slate-500">No orders yet.</p>}</div></section>
                <section className="rounded-xl border border-slate-200 bg-white p-5"><h2 className="font-bold text-slate-900">Top products</h2><div className="mt-3 divide-y">{top.length ? top.map((item) => <div key={`${item.product_id}-${item.sku}`} className="flex justify-between py-3"><div><p className="text-sm font-semibold">{item.product_name}</p><p className="text-xs text-slate-500">{item.units_sold} units sold</p></div><p className="text-sm font-bold">{formatMmk(item.revenue)}</p></div>) : <p className="py-6 text-sm text-slate-500">No completed sales yet.</p>}</div></section>
            </div>
        </div>
    );
}
