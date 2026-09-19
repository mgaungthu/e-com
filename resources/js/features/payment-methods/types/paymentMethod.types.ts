export type PaymentMethodType =
    | "cod"
    | "ewallet";

export type PaymentMethod = {
    id: number;

    name: string;
    code: string;
    type: PaymentMethodType;

    account_name: string | null;
    account_number: string | null;
    instructions: string | null;

    requires_proof: boolean;
    is_active: boolean;
    sort_order: number;

    logo_url: string | null;
    qr_image_url: string | null;

    created_at: string | null;
    updated_at: string | null;
};

export type PaymentMethodListResponse = {
    success: boolean;

    data: {
        data: PaymentMethod[];

        current_page: number;
        from: number | null;
        last_page: number;
        per_page: number;
        to: number | null;
        total: number;
    };
};

export type PaymentMethodResponse = {
    success: boolean;
    message?: string;

    data: {
        payment_method: PaymentMethod;
    };
};

export type PaymentMethodFilters = {
    search?: string;

    type?:
        | "all"
        | PaymentMethodType;

    status?:
        | "all"
        | "active"
        | "inactive";

    page?: number;
    per_page?: number;
};

export type PaymentMethodFormValues = {
    name: string;
    code: string;
    type: PaymentMethodType;

    account_name: string;
    account_number: string;
    instructions: string;

    logo: File | null;
    qr_image: File | null;

    remove_logo: boolean;
    remove_qr_image: boolean;

    requires_proof: boolean;
    is_active: boolean;

    sort_order: string;
};

export type PaymentMethodValidationErrors = Partial<
    Record<
        keyof PaymentMethodFormValues,
        string[]
    >
> & {
    [key: string]:
        | string[]
        | undefined;
};

export type PaymentMethodFormProps = {
    paymentMethod?: PaymentMethod | null;

    isSubmitting: boolean;

    validationErrors?: PaymentMethodValidationErrors;

    formError?: string | null;

    onSubmit: (
        values: PaymentMethodFormValues,
    ) => Promise<void> | void;
};