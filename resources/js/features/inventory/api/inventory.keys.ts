import type { InventoryFilters } from "@/features/inventory/types/inventory.types";

export const inventoryKeys = {
    all: ["inventory"] as const,
    lists: () => [...inventoryKeys.all, "list"] as const,
    list: (filters: InventoryFilters) =>
        [...inventoryKeys.lists(), filters] as const,
    histories: () => [...inventoryKeys.all, "history"] as const,
    history: (productId: number) =>
        [...inventoryKeys.histories(), productId] as const,
};
