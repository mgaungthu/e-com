export type CategoryParent = {
    id: number;
    name: string;
};

export type Category = {
    id: number;
    parent_id: number | null;
    parent: CategoryParent | null;
    name: string;
    slug: string;
    description: string | null;
    image_path: string | null;
    image_url: string | null;
    is_active: boolean;
    sort_order: number;
    seo_title: string | null;
    seo_description: string | null;
    created_at: string | null;
    updated_at: string | null;
};

export type CategoryListMeta = {
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
};

export type CategoryListResponse = {
    success: boolean;
    data: {
        data: Category[];
        current_page: number;
        from: number | null;
        last_page: number;
        per_page: number;
        to: number | null;
        total: number;
    };
};

export type CategoryResponse = {
    success: boolean;
    message?: string;
    data: {
        category: Category;
    };
};

export type CategoryDeleteResponse = {
    success: boolean;
    message: string;
};

export type CategoryFilters = {
    page?: number;
    search?: string;
    status?: "all" | "active" | "inactive";
    perPage?: number;
};

export type CategoryFormValues = {
    parent_id: string;
    name: string;
    slug: string;
    description: string;
    image: File | null;
    remove_image: boolean;
    is_active: boolean;
    sort_order: number;
    seo_title: string;
    seo_description: string;
};

export type CategoryValidationErrors = Record<string, string[]>;

export type CategoryFormErrorResponse = {
    message?: string;
    errors?: CategoryValidationErrors;
};

export type CategoryFormProps = {
    category?: Category | null;
    isSubmitting: boolean;
    submitLabel: string;
    onSubmit: (values: CategoryFormValues) => Promise<void>;
    onCancel: () => void;
};
