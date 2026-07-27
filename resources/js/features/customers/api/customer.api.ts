import { api } from "@/api/client";

import type {
    CustomerDetailResponse,
    CustomerListFilters,
    CustomerListResponse,
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

export const customerApi = {
    list,
    show,
};