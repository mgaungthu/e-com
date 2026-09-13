import { api } from "@/api/client";

import type {
    AdminOptionListResponse,
    HomeBannerFilters,
    HomeBannerFormValues,
    HomeBannerListResponse,
    HomeBannerProductOption,
    HomeBannerResponse,
    PaginationData,
} from "@/features/home-banners/types/homeBanner.types";

/*
|--------------------------------------------------------------------------
| Local Date Time -> ISO
|--------------------------------------------------------------------------
|
| datetime-local input gives a value using the administrator's local time.
|
| Convert it to an ISO timestamp before sending it to Laravel so that
| scheduling remains timezone-safe.
|
*/

function localDateTimeToIso(
    value: string,
): string | null {
    if (!value.trim()) {
        return null;
    }

    const date = new Date(value);

    if (
        Number.isNaN(
            date.getTime(),
        )
    ) {
        return value;
    }

    return date.toISOString();
}

/*
|--------------------------------------------------------------------------
| Build FormData
|--------------------------------------------------------------------------
*/

function buildHomeBannerFormData(
    values: HomeBannerFormValues,
): FormData {
    const formData =
        new FormData();

    /*
    |--------------------------------------------------------------------------
    | Banner Image
    |--------------------------------------------------------------------------
    |
    | During create, StoreHomeBannerRequest requires the image.
    |
    | During update, image is optional. If image is null, Laravel keeps the
    | existing image.
    |
    */

    if (values.image) {
        formData.append(
            "image",
            values.image,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Product
    |--------------------------------------------------------------------------
    */

    formData.append(
        "product_id",
        values.product_id,
    );

    /*
    |--------------------------------------------------------------------------
    | Display
    |--------------------------------------------------------------------------
    */

    formData.append(
        "sort_order",
        values.sort_order || "0",
    );

    formData.append(
        "is_active",
        values.is_active
            ? "1"
            : "0",
    );

    /*
    |--------------------------------------------------------------------------
    | Optional Schedule
    |--------------------------------------------------------------------------
    */

    const startsAt =
        localDateTimeToIso(
            values.starts_at,
        );

    const endsAt =
        localDateTimeToIso(
            values.ends_at,
        );

    if (startsAt) {
        formData.append(
            "starts_at",
            startsAt,
        );
    }

    if (endsAt) {
        formData.append(
            "ends_at",
            endsAt,
        );
    }

    return formData;
}

/*
|--------------------------------------------------------------------------
| Extract Admin Options
|--------------------------------------------------------------------------
|
| Existing admin endpoints may return either:
|
| data: [...]
|
| or:
|
| data: {
|     data: [...]
| }
|
*/

function extractOptions<T>(
    response:
        AdminOptionListResponse<T>,
): T[] {
    if (
        Array.isArray(
            response.data,
        )
    ) {
        return response.data;
    }

    return (
        response.data as PaginationData<T>
    ).data;
}

export const homeBannerApi = {
    /*
    |--------------------------------------------------------------------------
    | List
    |--------------------------------------------------------------------------
    */

    async list(
        filters: HomeBannerFilters,
    ): Promise<HomeBannerListResponse> {
        const response =
            await api.get<HomeBannerListResponse>(
                "/admin/home-banners",
                {
                    params:
                        filters,
                },
            );

        return response.data;
    },

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    async show(
        bannerId: number,
    ): Promise<HomeBannerResponse> {
        const response =
            await api.get<HomeBannerResponse>(
                `/admin/home-banners/${bannerId}`,
            );

        return response.data;
    },

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    async create(
        values: HomeBannerFormValues,
    ): Promise<HomeBannerResponse> {
        const formData =
            buildHomeBannerFormData(
                values,
            );

        const response =
            await api.post<HomeBannerResponse>(
                "/admin/home-banners",
                formData,
            );

        return response.data;
    },

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    |
    | Laravel route remains PUT.
    |
    | Use POST + _method=PUT for multipart/form-data image uploads.
    |
    */

    async update(
        bannerId: number,
        values: HomeBannerFormValues,
    ): Promise<HomeBannerResponse> {
        const formData =
            buildHomeBannerFormData(
                values,
            );

        formData.append(
            "_method",
            "PUT",
        );

        const response =
            await api.post<HomeBannerResponse>(
                `/admin/home-banners/${bannerId}`,
                formData,
            );

        return response.data;
    },

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    async remove(
        bannerId: number,
    ): Promise<void> {
        await api.delete(
            `/admin/home-banners/${bannerId}`,
        );
    },

    /*
    |--------------------------------------------------------------------------
    | Product Options
    |--------------------------------------------------------------------------
    |
    | Home banners can only link to products now.
    |
    */

    async productOptions():
        Promise<HomeBannerProductOption[]> {
        const response =
            await api.get<
                AdminOptionListResponse<HomeBannerProductOption>
            >(
                "/admin/products",
                {
                    params: {
                        per_page: 100,
                    },
                },
            );

        return extractOptions(
            response.data,
        ).filter(
            (product) =>
                product.is_active !==
                false,
        );
    },
};