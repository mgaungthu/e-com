export type InventoryStockStatus =
    | "in_stock"
    | "low_stock"
    | "out_of_stock";

export type InventoryTransactionType =
    | "addition"
    | "subtraction"
    | "set";

export type InventoryCategory = {
    id: number;
    name: string;
};

export type InventoryProduct = {
    id: number;
    category_id: number | null;
    category: InventoryCategory | null;
    name: string;
    sku: string;
    barcode: string | null;
    stock_quantity: number;
    low_stock_threshold: number;
    stock_status: InventoryStockStatus;
    image_url: string | null;
    is_active: boolean;
};

export type InventoryFilters = {
    page?: number;
    search?: string;
    category_id?: number | string;
    stock_status?: InventoryStockStatus | "all";
    per_page?: number;
};

export type InventoryPagination<T> = {
    data: T[];
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
};

export type InventoryListResponse = {
    success: boolean;
    data: InventoryPagination<InventoryProduct>;
};

export type InventoryAdjustmentValues = {
    type: InventoryTransactionType;
    quantity: number;
    reason: string;
    note: string;
};

export type InventoryUser = {
    id: number;
    name: string;
};

export type InventoryTransaction = {
    id: number;
    product_id: number;
    user_id: number | null;
    user: InventoryUser | null;
    type: InventoryTransactionType;
    quantity: number;
    quantity_before: number;
    quantity_after: number;
    reason: string | null;
    note: string | null;
    created_at: string;
    updated_at: string;
};

export type InventoryAdjustmentResponse = {
    success: boolean;
    message: string;
    data: {
        product: InventoryProduct;
        transaction: InventoryTransaction;
    };
};

export type InventoryHistoryResponse = {
    success: boolean;
    data: InventoryPagination<InventoryTransaction>;
};

export type InventoryValidationErrors = Record<
    string,
    string[] | undefined
>;

export type InventoryErrorResponse = {
    success?: boolean;
    message?: string;
    errors?: InventoryValidationErrors;
};
