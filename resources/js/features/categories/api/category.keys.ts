import type { CategoryFilters } from "@/features/categories/types/category.types";

export const categoryKeys = {
    all: ["categories"] as const,

    lists: () => [...categoryKeys.all, "list"] as const,

    list: (filters: CategoryFilters) =>
        [...categoryKeys.lists(), filters] as const,

    details: () => [...categoryKeys.all, "detail"] as const,

    detail: (categoryId: number) =>
        [...categoryKeys.details(), categoryId] as const,
};