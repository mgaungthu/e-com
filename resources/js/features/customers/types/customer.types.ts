export type CustomerStatus =
    | "active"
    | "inactive"
    | "blocked"
    | "pending";

export type CustomerOrderFilter =
    | "all"
    | "has_orders"
    | "no_orders";

export type CustomerProfile = {
    user_id: number;
    total_orders: number;
    total_spent: string;
    loyalty_points: number;
    last_order_at: string | null;
};

export type CustomerAddress = {
    id: number;
    user_id: number;
    type: string | null;
    first_name: string | null;
    last_name: string | null;
    company: string | null;
    phone: string | null;
    address_line_1: string;
    address_line_2: string | null;
    city: string;
    state: string | null;
    postal_code: string | null;
    country: string;
    is_default_shipping: boolean;
    is_default_billing: boolean;
    created_at: string | null;
    updated_at: string | null;
};

export type CustomerDevice = {
    id: number;
    platform: string | null;
    device_name: string | null;
    app_version: string | null;
    last_used_at: string | null;
};

export type CustomerPreference = {
    id: number;
    user_id: number;
    currency: string | null;
    language: string | null;
    marketing_email: boolean;
    marketing_sms: boolean;
};

export type CustomerNoteCreator = {
    id: number;
    name: string;
    first_name: string | null;
    last_name: string | null;
    display_name: string | null;
};

export type CustomerNote = {
    id: number;
    customer_id: number;
    created_by: number | null;
    note: string;
    is_pinned: boolean;
    created_at: string | null;
    updated_at: string | null;
    creator: CustomerNoteCreator | null;
};

export type Customer = {
    id: number;
    name: string;
    first_name: string | null;
    last_name: string | null;
    display_name: string | null;
    email: string;
    phone: string | null;
    avatar_path: string | null;
    avatar_url?: string | null;
    role: string;
    status: CustomerStatus;
    email_verified_at: string | null;
    phone_verified_at: string | null;
    last_login_at: string | null;
    last_login_ip: string | null;
    created_at: string | null;
    updated_at: string | null;

    addresses_count?: number;

    customer_profile?: CustomerProfile | null;
    customerProfile?: CustomerProfile | null;

    addresses?: CustomerAddress[];
    devices?: CustomerDevice[];
    preference?: CustomerPreference | null;
    customer_notes?: CustomerNote[];
    customerNotes?: CustomerNote[];
};

export type CustomerListFilters = {
    page?: number;
    perPage?: number;
    search?: string;
    status?: CustomerStatus | "all";
    orderStatus?: CustomerOrderFilter;
};

export type CustomerListData = {
    current_page: number;
    data: Customer[];
    first_page_url: string | null;
    from: number | null;
    last_page: number;
    last_page_url: string | null;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
};

export type CustomerListResponse = {
    success: boolean;
    data: CustomerListData;
};

export type CustomerDetailResponse = {
    success: boolean;
    data: {
        customer: Customer;
    };
};