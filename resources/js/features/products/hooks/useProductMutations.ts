import {
    useMutation,
    useQueryClient,
} from "@tanstack/react-query";

import { productApi } from "@/features/products/api/product.api";
import { productKeys } from "@/features/products/api/product.keys";
import type { ProductFormValues } from "@/features/products/types/product.types";

export function useCreateProduct() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (values: ProductFormValues) =>
            productApi.create(values),
        onSuccess: async () => {
            await queryClient.invalidateQueries({
                queryKey: productKeys.lists(),
            });
        },
    });
}

export function useUpdateProduct(productId: number) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (values: ProductFormValues) =>
            productApi.update(productId, values),
        onSuccess: async () => {
            await Promise.all([
                queryClient.invalidateQueries({
                    queryKey: productKeys.lists(),
                }),
                queryClient.invalidateQueries({
                    queryKey: productKeys.detail(productId),
                }),
            ]);
        },
    });
}

export function useDeleteProduct() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: productApi.remove,
        onSuccess: async () => {
            await queryClient.invalidateQueries({
                queryKey: productKeys.lists(),
            });
        },
    });
}
