import { useQuery } from "@tanstack/react-query";

import { customerApi } from "../api/customer.api";
import { customerKeys } from "../api/customer.keys";
import type { CustomerListFilters } from "../types/customer.types";

export function useCustomers(
    filters: CustomerListFilters,
) {
    return useQuery({
        queryKey: customerKeys.list(filters),
        queryFn: () =>
            customerApi.list(filters),
        placeholderData: (
            previousData,
        ) => previousData,
    });
}