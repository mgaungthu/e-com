import { useQuery } from "@tanstack/react-query";

import { orderApi } from "@/features/orders/api/order.api";
import { orderKeys } from "@/features/orders/api/order.keys";

export function useOrder(orderId: number | null) {
    return useQuery({
        queryKey: orderKeys.detail(orderId ?? 0),
        queryFn: () => orderApi.show(orderId as number),
        enabled: orderId !== null,
    });
}
