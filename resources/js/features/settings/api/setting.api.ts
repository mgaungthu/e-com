import { api } from "@/api/client";

export type Settings = {
    store_name: string; support_email: string; support_phone: string; currency: "MMK";
    timezone: string; order_prefix: string; default_low_stock_threshold: number;
    tax_rate: number; shipping_fee: number;
};

export const settingApi = {
    get: async (): Promise<{ data: Settings }> => (await api.get("/admin/settings")).data,
    update: async (settings: Settings) => (await api.put("/admin/settings", settings)).data,
};
