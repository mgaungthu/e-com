import { useQuery } from "@tanstack/react-query";

import { productApi } from "@/features/products/api/product.api";
import { productKeys } from "@/features/products/api/product.keys";

export function useProduct(productId: number | null) {
    return useQuery({
        queryKey: productKeys.detail(productId ?? 0),
        queryFn: () => productApi.show(productId as number),
        enabled: productId !== null,
    });
}
