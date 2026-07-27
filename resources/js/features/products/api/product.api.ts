import { api } from "@/api/client";

import type {
    ProductFilters,
    ProductFormValues,
    ProductListResponse,
    ProductResponse,
} from "@/features/products/types/product.types";

type ProductFormDataValue =
    | string
    | number
    | boolean
    | File
    | null
    | undefined;

function appendValue(
    formData: FormData,
    key: string,
    value: ProductFormDataValue,
    options?: {
        skipEmptyString?: boolean;
    },
) {
    if (value === null || value === undefined) {
        return;
    }

    if (
        options?.skipEmptyString &&
        typeof value === "string" &&
        value.trim() === ""
    ) {
        return;
    }

    if (value instanceof File) {
        formData.append(key, value);

        return;
    }

    if (typeof value === "boolean") {
        formData.append(key, value ? "1" : "0");

        return;
    }

    formData.append(key, String(value));
}

function toProductFormData(
    values: ProductFormValues,
): FormData {
    const formData = new FormData();

    appendValue(
        formData,
        "category_id",
        values.category_id,
        {
            skipEmptyString: true,
        },
    );

    appendValue(
        formData,
        "name",
        values.name.trim(),
    );

    appendValue(
        formData,
        "slug",
        values.slug,
        {
            skipEmptyString: true,
        },
    );

    appendValue(
        formData,
        "sku",
        values.sku.trim(),
    );

    appendValue(
        formData,
        "barcode",
        values.barcode,
        {
            skipEmptyString: true,
        },
    );

    appendValue(
        formData,
        "short_description",
        values.short_description,
        {
            skipEmptyString: true,
        },
    );

    appendValue(
        formData,
        "description",
        values.description,
        {
            skipEmptyString: true,
        },
    );

    appendValue(
        formData,
        "price",
        values.price,
    );

    appendValue(
        formData,
        "sale_price",
        values.sale_price,
        {
            skipEmptyString: true,
        },
    );

    appendValue(
        formData,
        "stock_quantity",
        values.stock_quantity,
    );

    appendValue(
        formData,
        "low_stock_threshold",
        values.low_stock_threshold,
    );

    appendValue(
        formData,
        "image",
        values.image,
    );

    if (values.remove_image) {
        appendValue(
            formData,
            "remove_image",
            true,
        );
    }

    appendValue(
        formData,
        "is_active",
        values.is_active,
    );

    appendValue(
        formData,
        "is_featured",
        values.is_featured,
    );

    appendValue(
        formData,
        "seo_title",
        values.seo_title,
        {
            skipEmptyString: true,
        },
    );

    appendValue(
        formData,
        "seo_description",
        values.seo_description,
        {
            skipEmptyString: true,
        },
    );

    return formData;
}

export const productApi = {
    async list(
        filters: ProductFilters,
    ): Promise<ProductListResponse> {
        const response =
            await api.get<ProductListResponse>(
                "/admin/products",
                {
                    params: filters,
                },
            );

        return response.data;
    },

    async show(
        productId: number,
    ): Promise<ProductResponse> {
        const response =
            await api.get<ProductResponse>(
                `/admin/products/${productId}`,
            );

        return response.data;
    },

    async create(
        values: ProductFormValues,
    ): Promise<ProductResponse> {
        const formData =
            toProductFormData(values);

        const response =
            await api.post<ProductResponse>(
                "/admin/products",
                formData,
                {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With":
                            "XMLHttpRequest",
                    },
                },
            );

        return response.data;
    },

    async update(
        productId: number,
        values: ProductFormValues,
    ): Promise<ProductResponse> {
        const formData =
            toProductFormData(values);

        /*
         * File upload ပါတဲ့ request ကို PHP/Laravel က
         * POST + _method=PUT အဖြစ် handle လုပ်တာ
         * ပိုတည်ငြိမ်ပါတယ်။
         */
        formData.append("_method", "PUT");

        const response =
            await api.post<ProductResponse>(
                `/admin/products/${productId}`,
                formData,
                {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With":
                            "XMLHttpRequest",
                    },
                },
            );

        return response.data;
    },

    async remove(
        productId: number,
    ): Promise<void> {
        await api.delete(
            `/admin/products/${productId}`,
            {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With":
                        "XMLHttpRequest",
                },
            },
        );
    },
};
