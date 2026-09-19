import { api } from "@/api/client";

import type {
    LocationFilters,
    LocationFormValues,
    LocationListResponse,
    LocationResponse,
} from "@/features/locations/types/location.types";

function buildLocationPayload(
    values: LocationFormValues,
) {
    return {
        parent_id:
            values.parent_id !== ""
                ? Number(
                      values.parent_id,
                  )
                : null,

        name_en:
            values.name_en.trim(),

        name_mm:
            values.name_mm.trim() ||
            null,

        type:
            values.type.trim(),

        shipping_fee:
            values.shipping_fee.trim() !== ""
                ? Number(
                      values.shipping_fee,
                  )
                : null,

        is_active:
            values.is_active,

        sort_order:
            values.sort_order,
    };
}

export async function getLocations(
    filters: LocationFilters,
): Promise<LocationListResponse> {
    const response =
        await api.get<LocationListResponse>(
            "/admin/locations",
            {
                params: {
                    page:
                        filters.page ??
                        1,

                    search:
                        filters.search ||
                        undefined,

                    status:
                        filters.status &&
                        filters.status !==
                            "all"
                            ? filters.status
                            : undefined,

                    type:
                        filters.type ||
                        undefined,

                    parent_id:
                        filters.parentId ??
                        undefined,

                    per_page:
                        filters.perPage ??
                        15,
                },
            },
        );

    return response.data;
}

export async function getLocation(
    locationId: number,
): Promise<LocationResponse> {
    const response =
        await api.get<LocationResponse>(
            `/admin/locations/${locationId}`,
        );

    return response.data;
}

export async function createLocation(
    values: LocationFormValues,
): Promise<LocationResponse> {
    const response =
        await api.post<LocationResponse>(
            "/admin/locations",
            buildLocationPayload(
                values,
            ),
        );

    return response.data;
}

export async function updateLocation(
    locationId: number,
    values: LocationFormValues,
): Promise<LocationResponse> {
    const response =
        await api.patch<LocationResponse>(
            `/admin/locations/${locationId}`,
            buildLocationPayload(
                values,
            ),
        );

    return response.data;
}