import type { PaymentMethodFilters } from "@/features/payment-methods/types/paymentMethod.types";

export const paymentMethodKeys = {
    all: [
        "payment-methods",
    ] as const,

    lists: () => [
        ...paymentMethodKeys.all,
        "list",
    ] as const,

    list: (
        filters: PaymentMethodFilters,
    ) => [
        ...paymentMethodKeys.lists(),
        filters,
    ] as const,

    details: () => [
        ...paymentMethodKeys.all,
        "detail",
    ] as const,

    detail: (
        paymentMethodId: number,
    ) => [
        ...paymentMethodKeys.details(),
        paymentMethodId,
    ] as const,
};