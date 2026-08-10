import { api } from "@/api/client";
import type {
    OrderFilters,
    OrderListResponse,
    OrderResponse,
    OrderStatus,
    PaymentStatus,
} from "@/features/orders/types/order.types";

export const orderApi = {
    async list(filters: OrderFilters): Promise<OrderListResponse> {
        const response = await api.get<OrderListResponse>("/admin/orders", {
            params: {
                ...filters,
                status: filters.status === "all" ? undefined : filters.status,
                payment_status:
                    filters.payment_status === "all"
                        ? undefined
                        : filters.payment_status,
            },
        });
        return response.data;
    },

    async show(orderId: number): Promise<OrderResponse> {
        const response = await api.get<OrderResponse>(`/admin/orders/${orderId}`);
        return response.data;
    },

    async updateStatus(
        orderId: number,
        status: OrderStatus,
        note: string,
    ): Promise<OrderResponse> {
        const response = await api.patch<OrderResponse>(
            `/admin/orders/${orderId}/status`,
            { status, note: note.trim() || null },
        );
        return response.data;
    },

    async updatePayment(
        orderId: number,
        paymentStatus: PaymentStatus,
    ): Promise<OrderResponse> {
        const response = await api.patch<OrderResponse>(
            `/admin/orders/${orderId}/payment`,
            { payment_status: paymentStatus },
        );
        return response.data;
    },
};
