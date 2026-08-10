import type {
    ProductFormValues,
    ProductValidationErrors,
} from "@/features/products/types/product.types";

export const EMPTY_PRODUCT_VALIDATION_ERRORS: ProductValidationErrors = {};

export const PRODUCT_FORM_INITIAL_VALUES: ProductFormValues = {
    category_id: "",
    name: "",
    slug: "",
    sku: "",
    barcode: "",
    short_description: "",
    description: "",
    price: "",
    sale_price: "",
    stock_quantity: "0",
    low_stock_threshold: "5",
    images: [],
    removed_image_ids: [],
    image_order: [],
    primary_image_id: null,
    primary_new_image_index: null,
    is_active: true,
    is_featured: false,
    seo_title: "",
    seo_description: "",
};

export const PRODUCT_VALIDATION_FIELD_ORDER: Array<
    keyof ProductFormValues
> = [
    "name",
    "category_id",
    "sku",
    "barcode",
    "price",
    "sale_price",
    "stock_quantity",
    "low_stock_threshold",
    "short_description",
    "description",
    "images",
    "is_active",
    "is_featured",
    "slug",
    "seo_title",
    "seo_description",
];
