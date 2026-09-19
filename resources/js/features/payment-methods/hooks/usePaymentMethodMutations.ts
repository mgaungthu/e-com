import {
    useMutation,
    useQueryClient,
} from "@tanstack/react-query";

import { paymentMethodApi } from "@/features/payment-methods/api/paymentMethod.api";
import { paymentMethodKeys } from "@/features/payment-methods/api/paymentMethod.keys";
import type { PaymentMethodFormValues } from "@/features/payment-methods/types/paymentMethod.types";

export function useCreatePaymentMethod() {
    const queryClient =
        useQueryClient();

    return useMutation({
        mutationFn: (
            values: PaymentMethodFormValues,
        ) =>
            paymentMethodApi.create(
                values,
            ),

        onSuccess: async () => {
            await queryClient.invalidateQueries(
                {
                    queryKey:
                        paymentMethodKeys.lists(),
                },
            );
        },
    });
}

export function useUpdatePaymentMethod(
    paymentMethodId: number,
) {
    const queryClient =
        useQueryClient();

    return useMutation({
        mutationFn: (
            values: PaymentMethodFormValues,
        ) =>
            paymentMethodApi.update(
                paymentMethodId,
                values,
            ),

        onSuccess: async () => {
            await Promise.all([
                queryClient.invalidateQueries({
                    queryKey:
                        paymentMethodKeys.lists(),
                }),

                queryClient.invalidateQueries({
                    queryKey:
                        paymentMethodKeys.detail(
                            paymentMethodId,
                        ),
                }),
            ]);
        },
    });
}

export function useDeletePaymentMethod() {
    const queryClient =
        useQueryClient();

    return useMutation({
        mutationFn:
            paymentMethodApi.remove,

        onSuccess: async () => {
            await queryClient.invalidateQueries(
                {
                    queryKey:
                        paymentMethodKeys.lists(),
                },
            );
        },
    });
}