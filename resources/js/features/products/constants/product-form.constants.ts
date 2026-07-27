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
    image: null,
    remove_image: false,
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
    "image",
    "is_active",
    "is_featured",
    "slug",
    "seo_title",
    "seo_description",
];
