import type { CategoryFormValues } from "@/features/categories/types/category.types";

export const CATEGORY_FORM_INITIAL_VALUES: CategoryFormValues = {
    parent_id: "",
    name: "",
    slug: "",
    description: "",
    image: null,
    remove_image: false,
    is_active: true,
    sort_order: 0,
    seo_title: "",
    seo_description: "",
};

export const CATEGORY_VALIDATION_FIELD_ORDER: Array<
    keyof CategoryFormValues
> = [
    "name",
    "slug",
    "parent_id",
    "description",
    "seo_title",
    "seo_description",
    "image",
    "sort_order",
    "is_active",
];
