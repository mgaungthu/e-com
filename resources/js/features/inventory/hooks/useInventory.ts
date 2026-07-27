import { useQuery } from "@tanstack/react-query";

import { getInventory } from "@/features/inventory/api/inventory.api";
import { inventoryKeys } from "@/features/inventory/api/inventory.keys";
import type { InventoryFilters } from "@/features/inventory/types/inventory.types";

export function useInventory(filters: InventoryFilters) {
    return useQuery({
        queryKey: inventoryKeys.list(filters),
        queryFn: () => getInventory(filters),
    });
}
