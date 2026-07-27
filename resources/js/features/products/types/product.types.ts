export type ProductCategory = {
    id: number;
    name: string;
};

export type ProductStockStatus =
    | 'in_stock'
    | 'low_stock'
    | 'out_of_stock';

export type Product = {
    id: number;
    category_id: number | null;
    category: ProductCategory | null;

    name: string;
    slug: string;
    sku: string;
    barcode: string | null;

    short_description: string | null;
    description: string | null;

    price: string;
    sale_price: string | null;

    stock_quantity: number;
    low_stock_threshold: number;
    stock_status: ProductStockStatus;

    image_path: string | null;
    image_url: string | null;

    is_active: boolean;
    is_featured: boolean;

    seo_title: string | null;
    seo_description: string | null;

    created_at: string | null;
    updated_at: string | null;
};

export type ProductListResponse = {
    success: boolean;
    data: {
        data: Product[];
        current_page: number;
        from: number | null;
        last_page: number;
        per_page: number;
        to: number | null;
        total: number;
    };
};

export type ProductResponse = {
    success: boolean;
    message?: string;
    data: {
        product: Product;
    };
};

export type ProductFilters = {
    search?: string;
    category_id?: number | string;
    status?: 'all' | 'active' | 'inactive';
    stock_status?:
        | 'all'
        | 'in_stock'
        | 'low_stock'
        | 'out_of_stock';
    page?: number;
    per_page?: number;
};

export type ProductFormValues = {
    category_id: string;
    name: string;
    slug: string;
    sku: string;
    barcode: string;
    short_description: string;
    description: string;
    price: string;
    sale_price: string;
    stock_quantity: string;
    low_stock_threshold: string;
    image: File | null;
    remove_image: boolean;
    is_active: boolean;
    is_featured: boolean;
    seo_title: string;
    seo_description: string;
};

export type ProductCategoryOption = {
    id: number;
    name: string;
};

export type ProductValidationErrors = Partial<
    Record<keyof ProductFormValues, string[]>
> & {
    [key: string]: string[] | undefined;
};

export type ProductFormProps = {
    product?: Product | null;
    categories: ProductCategoryOption[];
    isSubmitting: boolean;
    validationErrors?: ProductValidationErrors;
    formError?: string | null;
    onSubmit: (values: ProductFormValues) => Promise<void> | void;
};
