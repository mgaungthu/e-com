import { useQuery } from "@tanstack/react-query";

import { getCategories } from "@/features/categories/api/category.api";
import { categoryKeys } from "@/features/categories/api/category.keys";
import type { CategoryFilters } from "@/features/categories/types/category.types";

export function useCategories(filters: CategoryFilters) {
    return useQuery({
        queryKey: categoryKeys.list(filters),
        queryFn: () => getCategories(filters),
    });
}