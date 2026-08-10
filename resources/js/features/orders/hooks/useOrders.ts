import { keepPreviousData, useQuery } from "@tanstack/react-query";

import { orderApi } from "@/features/orders/api/order.api";
import { orderKeys } from "@/features/orders/api/order.keys";
import type { OrderFilters } from "@/features/orders/types/order.types";

export function useOrders(filters: OrderFilters) {
    return useQuery({
        queryKey: orderKeys.list(filters),
        queryFn: () => orderApi.list(filters),
        placeholderData: keepPreviousData,
    });
}
