import { useQuery } from "@tanstack/react-query";

import { customerApi } from "../api/customer.api";
import { customerKeys } from "../api/customer.keys";

export function useCustomer(
    customerId?: number | string,
) {
    return useQuery({
        queryKey: customerKeys.detail(
            customerId ?? "",
        ),
        queryFn: () =>
            customerApi.show(customerId!),
        enabled: Boolean(customerId),
    });
}