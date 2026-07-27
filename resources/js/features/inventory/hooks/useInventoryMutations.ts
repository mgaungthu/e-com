import {
    useMutation,
    useQueryClient,
} from "@tanstack/react-query";

import { adjustInventory } from "@/features/inventory/api/inventory.api";
import { inventoryKeys } from "@/features/inventory/api/inventory.keys";
import type { InventoryAdjustmentValues } from "@/features/inventory/types/inventory.types";

export function useAdjustInventory() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: ({
            productId,
            values,
        }: {
            productId: number;
            values: InventoryAdjustmentValues;
        }) => adjustInventory(productId, values),
        onSuccess: async (_response, variables) => {
            await Promise.all([
                queryClient.invalidateQueries({
                    queryKey: inventoryKeys.lists(),
                }),
                queryClient.invalidateQueries({
                    queryKey: inventoryKeys.history(
                        variables.productId,
                    ),
                }),
            ]);
        },
    });
}
