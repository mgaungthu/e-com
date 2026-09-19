import { useQuery } from "@tanstack/react-query";

import { paymentMethodApi } from "@/features/payment-methods/api/paymentMethod.api";
import { paymentMethodKeys } from "@/features/payment-methods/api/paymentMethod.keys";

export function usePaymentMethod(
    paymentMethodId: number | null,
) {
    return useQuery({
        queryKey:
            paymentMethodKeys.detail(
                paymentMethodId ?? 0,
            ),

        queryFn: () =>
            paymentMethodApi.show(
                paymentMethodId as number,
            ),

        enabled:
            paymentMethodId !== null,
    });
}