import type { ProductFilters } from "@/features/products/types/product.types";

export const productKeys = {
    all: ['products'] as const,

    lists: () => [
        ...productKeys.all,
        'list',
    ] as const,

    list: (filters: ProductFilters) => [
        ...productKeys.lists(),
        filters,
    ] as const,

    details: () => [
        ...productKeys.all,
        'detail',
    ] as const,

    detail: (productId: number) => [
        ...productKeys.details(),
        productId,
    ] as const,
};
