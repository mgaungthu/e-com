import {
    useMutation,
    useQueryClient,
} from "@tanstack/react-query";

import {
    createCategory,
    deleteCategory,
    updateCategory,
} from "@/features/categories/api/category.api";
import { categoryKeys } from "@/features/categories/api/category.keys";
import type { CategoryFormValues } from "@/features/categories/types/category.types";

export function useCreateCategory() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: createCategory,
        onSuccess: async () => {
            await queryClient.invalidateQueries({
                queryKey: categoryKeys.lists(),
            });
        },
    });
}

export function useUpdateCategory(categoryId: number) {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (values: CategoryFormValues) =>
            updateCategory(categoryId, values),

        onSuccess: async () => {
            await Promise.all([
                queryClient.invalidateQueries({
                    queryKey: categoryKeys.lists(),
                }),
                queryClient.invalidateQueries({
                    queryKey: categoryKeys.detail(categoryId),
                }),
            ]);
        },
    });
}

export function useDeleteCategory() {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: deleteCategory,
        onSuccess: async () => {
            await queryClient.invalidateQueries({
                queryKey: categoryKeys.lists(),
            });
        },
    });
}