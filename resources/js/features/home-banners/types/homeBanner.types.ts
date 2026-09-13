export type HomeBannerProduct = {
    id: number;
    name: string;
    slug: string;
    is_active?: boolean;
};

export type HomeBanner = {
    id: number;

    image_path: string;
    image_url: string | null;

    product_id: number;
    product: HomeBannerProduct;

    sort_order: number;
    is_active: boolean;

    starts_at: string | null;
    ends_at: string | null;

    created_at: string | null;
    updated_at: string | null;
};

export type HomeBannerFormValues = {
    image: File | null;

    product_id: string;

    sort_order: string;

    is_active: boolean;

    starts_at: string;
    ends_at: string;
};

export type HomeBannerFilters = {
    page?: number;
    per_page?: number;

    search?: string;

    is_active?: boolean;
};

export type PaginationData<T> = {
    current_page: number;
    data: T[];

    first_page_url?: string | null;
    from: number | null;

    last_page: number;
    last_page_url?: string | null;

    next_page_url?: string | null;

    path?: string;

    per_page: number;

    prev_page_url?: string | null;

    to: number | null;
    total: number;
};

export type HomeBannerListResponse = {
    success: boolean;

    data: PaginationData<HomeBanner>;
};

export type HomeBannerResponse = {
    success: boolean;

    message?: string;

    data: {
        banner: HomeBanner;
    };
};

export type HomeBannerProductOption = {
    id: number;
    name: string;
    slug: string;
    is_active?: boolean;
};

export type AdminOptionListResponse<T> = {
    success: boolean;

    data:
        | PaginationData<T>
        | T[];
};

export type LaravelValidationErrors =
    Record<string, string[]>;

export type LaravelErrorResponse = {
    message?: string;

    errors?: LaravelValidationErrors;
};