import { api } from "@/api/client";
import type {
    InventoryAdjustmentResponse,
    InventoryAdjustmentValues,
    InventoryFilters,
    InventoryHistoryResponse,
    InventoryListResponse,
} from "@/features/inventory/types/inventory.types";

export async function getInventory(
    filters: InventoryFilters,
): Promise<InventoryListResponse> {
    const response = await api.get<InventoryListResponse>(
        "/admin/inventory",
        {
            params: {
                page: filters.page ?? 1,
                search: filters.search || undefined,
                category_id: filters.category_id || undefined,
                stock_status:
                    filters.stock_status &&
                    filters.stock_status !== "all"
                        ? filters.stock_status
                        : undefined,
                per_page: filters.per_page ?? 15,
            },
        },
    );

    return response.data;
}

export async function adjustInventory(
    productId: number,
    values: InventoryAdjustmentValues,
): Promise<InventoryAdjustmentResponse> {
    const response = await api.post<InventoryAdjustmentResponse>(
        `/admin/inventory/${productId}/adjust`,
        {
            ...values,
            reason: values.reason.trim() || null,
            note: values.note.trim() || null,
        },
    );

    return response.data;
}

export async function getInventoryHistory(
    productId: number,
    page = 1,
    perPage = 15,
): Promise<InventoryHistoryResponse> {
    const response = await api.get<InventoryHistoryResponse>(
        `/admin/inventory/${productId}/history`,
        {
            params: {
                page,
                per_page: perPage,
            },
        },
    );

    return response.data;
}
