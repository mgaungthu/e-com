import { api } from "@/api/client";

export type DashboardData = {
    summary: {
        total_orders: number; today_orders: number; total_revenue: string | number;
        total_customers: number; total_products: number; low_stock_products: number;
    };
    order_statuses: Record<string, number>;
    sales_trend: Array<{ date: string; orders: number; revenue: string | number }>;
    recent_orders: Array<{ id: number; order_number: string; status: string; grand_total: string; created_at: string; customer: { name: string; display_name: string | null } | null }>;
    top_products: Array<{ product_id: number | null; product_name: string; sku: string; units_sold: number; revenue: string }>;
};

export async function getDashboard(): Promise<{ success: boolean; data: DashboardData }> {
    return (await api.get("/admin/dashboard")).data;
}
