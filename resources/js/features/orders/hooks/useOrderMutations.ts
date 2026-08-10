import { useMutation, useQueryClient } from "@tanstack/react-query";

import { orderApi } from "@/features/orders/api/order.api";
import { orderKeys } from "@/features/orders/api/order.keys";
import type { OrderStatus, PaymentStatus } from "@/features/orders/types/order.types";

export function useUpdateOrderStatus(orderId: number) {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: ({ status, note }: { status: OrderStatus; note: string }) =>
            orderApi.updateStatus(orderId, status, note),
        onSuccess: async () => {
            await Promise.all([
                queryClient.invalidateQueries({ queryKey: orderKeys.lists() }),
                queryClient.invalidateQueries({ queryKey: orderKeys.detail(orderId) }),
            ]);
        },
    });
}

export function useUpdateOrderPayment(orderId: number) {
    const queryClient = useQueryClient();
    return useMutation({
        mutationFn: (status: PaymentStatus) =>
            orderApi.updatePayment(orderId, status),
        onSuccess: async () => {
            await Promise.all([
                queryClient.invalidateQueries({ queryKey: orderKeys.lists() }),
                queryClient.invalidateQueries({ queryKey: orderKeys.detail(orderId) }),
            ]);
        },
    });
}
