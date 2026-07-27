import { useQuery } from "@tanstack/react-query";

import { getCategory } from "@/features/categories/api/category.api";
import { categoryKeys } from "@/features/categories/api/category.keys";

export function useCategory(categoryId: number | null) {
    return useQuery({
        queryKey: categoryKeys.detail(categoryId ?? 0),
        queryFn: () => getCategory(categoryId as number),
        enabled: categoryId !== null,
    });
}