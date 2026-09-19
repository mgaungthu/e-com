import { useQuery } from "@tanstack/react-query";

import { paymentMethodApi } from "@/features/payment-methods/api/paymentMethod.api";
import { paymentMethodKeys } from "@/features/payment-methods/api/paymentMethod.keys";
import type { PaymentMethodFilters } from "@/features/payment-methods/types/paymentMethod.types";

export function usePaymentMethods(
    filters: PaymentMethodFilters,
) {
    return useQuery({
        queryKey:
            paymentMethodKeys.list(
                filters,
            ),

        queryFn: () =>
            paymentMethodApi.list(
                filters,
            ),
    });
}