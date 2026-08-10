import { useState } from "react";

import { useQuery } from "@tanstack/react-query";
import { Download } from "lucide-react";

import { exportReport, getReports } from "@/features/reports/api/report.api";
import { useAuthStore } from "@/store/authStore";
import { formatMmk } from "@/utils/currency";

export default function ReportsPage() {
    const [dateFrom, setDateFrom] = useState("");
    const [dateTo, setDateTo] = useState("");
    const canExport = useAuthStore((state) => state.user?.permissions.includes("reports.export") ?? false);
    const query = useQuery({ queryKey: ["reports", dateFrom, dateTo], queryFn: () => getReports(dateFrom, dateTo) });
    if (query.isLoading) return <div className="p-10 text-center text-slate-500">Loading reports...</div>;
    if (query.isError || !query.data) return <div className="p-10 text-center text-red-600">Unable to load reports.</div>;
    const report = query.data.data;

    return <div className="space-y-6"><div className="flex flex-wrap items-end justify-between gap-4"><div><h1 className="text-2xl font-bold">Reports</h1><p className="mt-1 text-sm text-slate-500">Sales, inventory valuation, and customer performance.</p></div><div className="flex gap-2"><input type="date" value={dateFrom} onChange={(event) => setDateFrom(event.target.value)} className="rounded-lg border px-3 py-2 text-sm" /><input type="date" value={dateTo} onChange={(event) => setDateTo(event.target.value)} className="rounded-lg border px-3 py-2 text-sm" /></div></div>
        <div className="grid gap-4 md:grid-cols-4">{[["Orders", report.sales.orders], ["Revenue", formatMmk(report.sales.revenue)], ["Average order", formatMmk(report.sales.average_order_value)], ["Discounts", formatMmk(report.sales.discounts)]].map(([label, value]) => <div key={label} className="rounded-xl border bg-white p-5"><p className="text-sm text-slate-500">{label}</p><p className="mt-2 text-xl font-bold">{value}</p></div>)}</div>
        <ReportSection title="Inventory report" exportType="inventory" canExport={canExport} onExport={() => exportReport("inventory", dateFrom, dateTo)}><div className="overflow-x-auto"><table className="min-w-full divide-y"><thead><tr>{['Product', 'SKU', 'Category', 'Stock', 'Status', 'Value'].map((item) => <th key={item} className="px-4 py-3 text-left text-xs uppercase text-slate-500">{item}</th>)}</tr></thead><tbody className="divide-y">{report.inventory.map((item) => <tr key={item.id}><td className="px-4 py-3 text-sm font-semibold">{item.name}</td><td className="px-4 py-3 text-sm">{item.sku}</td><td className="px-4 py-3 text-sm">{item.category ?? "—"}</td><td className="px-4 py-3 text-sm">{item.stock_quantity}</td><td className="px-4 py-3 text-sm capitalize">{item.stock_status.replaceAll("_", " ")}</td><td className="px-4 py-3 text-sm font-bold">{formatMmk(item.stock_value)}</td></tr>)}</tbody></table></div></ReportSection>
        <ReportSection title="Top customers" exportType="customers" canExport={canExport} onExport={() => exportReport("customers", dateFrom, dateTo)}><div className="divide-y">{report.customers.map((item) => <div key={item.id} className="flex justify-between p-4"><div><p className="text-sm font-semibold">{item.user?.display_name || item.user?.name || "Customer"}</p><p className="text-xs text-slate-500">{item.user?.email} · {item.total_orders} orders</p></div><p className="font-bold">{formatMmk(item.total_spent)}</p></div>)}</div></ReportSection>
        {canExport ? <button type="button" onClick={() => exportReport("sales", dateFrom, dateTo)} className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white"><Download size={16} />Export sales CSV</button> : null}
    </div>;
}

function ReportSection({ title, canExport, onExport, children }: { title: string; exportType: string; canExport: boolean; onExport: () => void; children: React.ReactNode }) {
    return <section className="overflow-hidden rounded-xl border bg-white"><div className="flex items-center justify-between border-b p-5"><h2 className="font-bold">{title}</h2>{canExport ? <button type="button" onClick={onExport} className="inline-flex items-center gap-2 text-sm font-semibold text-blue-600"><Download size={15} />CSV</button> : null}</div>{children}</section>;
}
