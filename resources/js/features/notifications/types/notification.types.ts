export type NotificationType =
    | "new_product"
    | "product_restocked"
    | "general";

export type NotificationProductOption = {
    id: number;
    name: string;
    slug: string;
    sku: string;
    stock_quantity: number;
    is_active: boolean;
};

export type NotificationFormValues = {
    type: NotificationType;

    product_id: string;

    title: string;

    body: string;
};

export type NotificationProductsResponse = {
    success: boolean;

    data: {
        products: NotificationProductOption[];
    };
};

export type SendNotificationResponse = {
    success: boolean;

    message: string;

    data: {
        notification: {
            type: NotificationType;

            title: string;

            body: string;

            product: {
                id: number;
                name: string;
                slug: string;
            } | null;

            recipient_count: number;
        };
    };
};

export type LaravelValidationErrors =
    Record<string, string[]>;

export type LaravelErrorResponse = {
    message?: string;

    errors?: LaravelValidationErrors;
};