import { api } from "@/api/client";

import type {
    CustomerDetailResponse,
    CustomerListFilters,
    CustomerListResponse,
    CustomerNote,
    CustomerStatus,
    CustomerUpdateValues,
} from "../types/customer.types";

async function list(
    filters: CustomerListFilters = {},
): Promise<CustomerListResponse> {
    const response =
        await api.get<CustomerListResponse>(
            "/admin/customers",
            {
                params: {
                    page: filters.page ?? 1,
                    per_page:
                        filters.perPage ?? 15,
                    search:
                        filters.search?.trim() ||
                        undefined,
                    status:
                        filters.status &&
                        filters.status !== "all"
                            ? filters.status
                            : undefined,
                    order_status:
                        filters.orderStatus &&
                        filters.orderStatus !== "all"
                            ? filters.orderStatus
                            : undefined,
                },
            },
        );

    return response.data;
}

async function show(
    customerId: number | string,
): Promise<CustomerDetailResponse> {
    const response =
        await api.get<CustomerDetailResponse>(
            `/admin/customers/${customerId}`,
        );

    return response.data;
}

async function update(customerId: number, values: CustomerUpdateValues) {
    return (await api.patch(`/admin/customers/${customerId}`, values)).data;
}

async function updateStatus(customerId: number, status: CustomerStatus) {
    return (await api.patch(`/admin/customers/${customerId}/status`, { status })).data;
}

async function createNote(customerId: number, note: string, isPinned: boolean) {
    return (await api.post(`/admin/customers/${customerId}/notes`, { note, is_pinned: isPinned })).data;
}

async function updateNote(customerId: number, note: CustomerNote) {
    return (await api.patch(`/admin/customers/${customerId}/notes/${note.id}`, {
        note: note.note,
        is_pinned: note.is_pinned,
    })).data;
}

async function deleteNote(customerId: number, noteId: number) {
    return (await api.delete(`/admin/customers/${customerId}/notes/${noteId}`)).data;
}

export const customerApi = {
    list,
    show,
    update,
    updateStatus,
    createNote,
    updateNote,
    deleteNote,
};
