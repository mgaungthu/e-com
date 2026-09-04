export type OrderStatus =
    | "pending"
    | "confirmed"
    | "processing"
    | "shipped"
    | "delivered"
    | "cancelled";

export type PaymentStatus =
    | "pending"
    | "paid"
    | "failed"
    | "refunded";

export type OrderCustomer = {
    id: number;
    name: string;
    first_name: string | null;
    last_name: string | null;
    display_name: string | null;
    email: string;
    phone?: string | null;
};

export type OrderProduct = {
    id: number;
    name: string;
    image_url: string | null;
};

export type OrderItem = {
    id: number;
    product_id: number | null;
    product_name: string;
    sku: string;
    unit_price: string;
    quantity: number;
    line_total: string;
    product: OrderProduct | null;
};

export type OrderStatusHistory = {
    id: number;
    from_status: OrderStatus | null;
    to_status: OrderStatus;
    note: string | null;
    created_at: string;
    user: OrderCustomer | null;
};

export type OrderAddress = {
    id?: number | null;

    name?: string | null;
    full_name?: string | null;
    recipient_name?: string | null;

    phone?: string | null;

    type?: string | null;

    unit?: string | null;
    street?: string | null;
    address?: string | null;
    address_line_one?: string | null;
    address_line_two?: string | null;

    township?: string | null;
    district?: string | null;
    city?: string | null;
    state?: string | null;
    region?: string | null;

    postal_code?: string | null;
    country?: string | null;
};

export type OrderPayment = {
    id: number;
    order_id: number;
    payment_method_id: number | null;
    method_code: string | null;
    method_name: string | null;
    amount: string;
    reference_number: string | null;
    proof_image_path: string | null;
    proof_image_url: string | null;
    status: string;
    rejection_reason: string | null;
    submitted_at: string | null;
    reviewed_at: string | null;
    reviewed_by: number | null;
    created_at: string | null;
    updated_at: string | null;
};

export type Order = {
    id: number;
    order_number: string;
    user_id: number | null;

    customer: OrderCustomer | null;

    shipping_address: OrderAddress | null;
    billing_address: OrderAddress | null;

    status: OrderStatus;
    payment_status: PaymentStatus;

    payment_method: string | null;

    subtotal: string;
    discount_total: string;
    shipping_total: string;
    tax_total: string;
    grand_total: string;

    notes: string | null;

    items_count?: number;
    items?: OrderItem[];

    status_histories?: OrderStatusHistory[];

    latest_payment: OrderPayment | null;

    available_statuses: OrderStatus[];
    available_payment_statuses: PaymentStatus[];

    confirmed_at: string | null;
    processing_at: string | null;
    shipped_at: string | null;
    delivered_at: string | null;
    cancelled_at: string | null;
    paid_at: string | null;

    created_at: string;
    updated_at: string;
};

export type OrderFilters = {
    page?: number;
    per_page?: number;
    search?: string;
    status?: OrderStatus | "all";
    payment_status?: PaymentStatus | "all";
    date_from?: string;
    date_to?: string;
};

export type OrderPagination = {
    data: Order[];
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
};

export type OrderListResponse = {
    success: boolean;
    data: OrderPagination;
};

export type OrderResponse = {
    success: boolean;
    message?: string;
    data: {
        order: Order;
    };
};