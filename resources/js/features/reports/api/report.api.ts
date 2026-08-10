import { api } from "@/api/client";

export type ReportData = {
    period: { from: string; to: string };
    sales: { orders: number; revenue: string | number; average_order_value: string | number; discounts: string | number; shipping: string | number; daily: Array<{ created_at: string; grand_total: string }> };
    inventory: Array<{ id: number; name: string; sku: string; category: string | null; stock_quantity: number; stock_status: string; stock_value: number }>;
    customers: Array<{ id: number; total_orders: number; total_spent: string; last_order_at: string | null; user: { name: string; display_name: string | null; email: string } | null }>;
};

export async function getReports(dateFrom: string, dateTo: string): Promise<{ data: ReportData }> {
    return (await api.get("/admin/reports", { params: { date_from: dateFrom || undefined, date_to: dateTo || undefined } })).data;
}

export async function exportReport(type: string, dateFrom: string, dateTo: string) {
    const response = await api.get("/admin/reports/export", { params: { type, date_from: dateFrom || undefined, date_to: dateTo || undefined }, responseType: "blob" });
    const url = URL.createObjectURL(response.data);
    const link = document.createElement("a");
    link.href = url;
    link.download = `${type}-report.csv`;
    link.click();
    URL.revokeObjectURL(url);
}
