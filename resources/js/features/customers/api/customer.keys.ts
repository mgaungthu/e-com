import type { CustomerListFilters } from "../types/customer.types";

export const customerKeys = {
    all: ["customers"] as const,

    lists: () =>
        [...customerKeys.all, "list"] as const,

    list: (filters: CustomerListFilters) =>
        [
            ...customerKeys.lists(),
            filters,
        ] as const,

    details: () =>
        [...customerKeys.all, "detail"] as const,

    detail: (customerId: number | string) =>
        [
            ...customerKeys.details(),
            String(customerId),
        ] as const,
};