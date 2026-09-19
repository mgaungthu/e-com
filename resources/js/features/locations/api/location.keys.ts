import type { LocationFilters } from "@/features/locations/types/location.types";

export const locationKeys = {
    all: [
        "locations-admin",
    ] as const,

    lists: () =>
        [
            ...locationKeys.all,
            "list",
        ] as const,

    list: (
        filters: LocationFilters,
    ) =>
        [
            ...locationKeys.lists(),
            filters,
        ] as const,

    details: () =>
        [
            ...locationKeys.all,
            "detail",
        ] as const,

    detail: (
        locationId: number,
    ) =>
        [
            ...locationKeys.details(),
            locationId,
        ] as const,
};