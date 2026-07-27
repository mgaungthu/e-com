import { api } from "@/api/client";

import type {
    CategoryDeleteResponse,
    CategoryFilters,
    CategoryFormValues,
    CategoryListResponse,
    CategoryResponse,
} from "@/features/categories/types/category.types";

function buildCategoryFormData(values: CategoryFormValues): FormData {
    const formData = new FormData();

    formData.append("parent_id", values.parent_id || "");
    formData.append("name", values.name.trim());
    formData.append("slug", values.slug.trim());
    formData.append("description", values.description);
    formData.append("is_active", values.is_active ? "1" : "0");
    formData.append("sort_order", String(values.sort_order));
    formData.append("seo_title", values.seo_title);
    formData.append("seo_description", values.seo_description);

    if (values.image instanceof File) {
        formData.append("image", values.image);
    }

    if (values.remove_image) {
        formData.append("remove_image", "1");
    }

    return formData;
}

export async function getCategories(
    filters: CategoryFilters,
): Promise<CategoryListResponse> {
    const response = await api.get<CategoryListResponse>(
        "/admin/categories",
        {
            params: {
                page: filters.page ?? 1,
                search: filters.search || undefined,
                status:
                    filters.status && filters.status !== "all"
                        ? filters.status
                        : undefined,
                per_page: filters.perPage ?? 15,
            },
        },
    );

    return response.data;
}

export async function getCategory(
    categoryId: number,
): Promise<CategoryResponse> {
    const response = await api.get<CategoryResponse>(
        `/admin/categories/${categoryId}`,
    );

    return response.data;
}

export async function createCategory(
    values: CategoryFormValues,
): Promise<CategoryResponse> {
    const formData = buildCategoryFormData(values);

    const response = await api.post<CategoryResponse>(
        "/admin/categories",
        formData,
    );

    return response.data;
}

export async function updateCategory(
    categoryId: number,
    values: CategoryFormValues,
): Promise<CategoryResponse> {
    const formData = buildCategoryFormData(values);

    // web.php မှာ update route က POST ဖြစ်ရင် _method မထည့်ပါနဲ့။
    const response = await api.post<CategoryResponse>(
        `/admin/categories/${categoryId}`,
        formData,
    );

    return response.data;
}

export async function deleteCategory(
    categoryId: number,
): Promise<CategoryDeleteResponse> {
    const response = await api.delete<CategoryDeleteResponse>(
        `/admin/categories/${categoryId}`,
    );

    return response.data;
}