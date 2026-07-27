import { useQuery } from "@tanstack/react-query";

import { productApi } from "@/features/products/api/product.api";
import { productKeys } from "@/features/products/api/product.keys";
import type { ProductFilters } from "@/features/products/types/product.types";

export function useProducts(
    filters: ProductFilters,
) {
    return useQuery({
        queryKey: productKeys.list(filters),
        queryFn: () => productApi.list(filters),
    });
}
