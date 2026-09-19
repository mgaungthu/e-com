export type LocationParent = {
    id: number;
    name_en: string;
    name_mm: string | null;
    type: string;
};

export type Location = {
    id: number;

    parent_id: number | null;

    parent: LocationParent | null;

    name_en: string;

    name_mm: string | null;

    type: string;

    shipping_fee: number | null;

    is_active: boolean;

    sort_order: number;

    children_count: number;

    created_at: string | null;

    updated_at: string | null;
};

export type LocationFilters = {
    page?: number;

    search?: string;

    status?:
        | "all"
        | "active"
        | "inactive";

    type?: string;

    parentId?: number;

    perPage?: number;
};

export type LocationListResponse = {
    success: boolean;

    data: {
        data: Location[];

        current_page: number;

        from: number | null;

        last_page: number;

        per_page: number;

        to: number | null;

        total: number;
    };
};

export type LocationResponse = {
    success: boolean;

    message?: string;

    data: {
        location: Location;
    };
};

export type LocationFormValues = {
    parent_id: string;

    name_en: string;

    name_mm: string;

    type: string;

    shipping_fee: string;

    is_active: boolean;

    sort_order: number;
};

export type LocationValidationErrors =
    Record<string, string[]>;

export type LocationFormErrorResponse = {
    message?: string;

    errors?: LocationValidationErrors;
};

export type LocationFormProps = {
    location?: Location | null;

    isSubmitting: boolean;

    submitLabel: string;

    onSubmit: (
        values: LocationFormValues,
    ) => Promise<void>;

    onCancel: () => void;
};