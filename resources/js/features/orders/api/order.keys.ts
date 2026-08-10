import type { OrderFilters } from "@/features/orders/types/order.types";

export const orderKeys = {
    all: ["orders"] as const,
    lists: () => [...orderKeys.all, "list"] as const,
    list: (filters: OrderFilters) => [...orderKeys.lists(), filters] as const,
    details: () => [...orderKeys.all, "detail"] as const,
    detail: (orderId: number) => [...orderKeys.details(), orderId] as const,
};
