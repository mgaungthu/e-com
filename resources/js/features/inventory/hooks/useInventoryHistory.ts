import { useQuery } from "@tanstack/react-query";

import { getInventoryHistory } from "@/features/inventory/api/inventory.api";
import { inventoryKeys } from "@/features/inventory/api/inventory.keys";

export function useInventoryHistory(
    productId: number | null,
    page: number,
    enabled = true,
) {
    return useQuery({
        queryKey: [
            ...inventoryKeys.history(productId ?? 0),
            { page },
        ],
        queryFn: () =>
            getInventoryHistory(productId as number, page),
        enabled:
            enabled &&
            productId !== null &&
            productId > 0,
    });
}
