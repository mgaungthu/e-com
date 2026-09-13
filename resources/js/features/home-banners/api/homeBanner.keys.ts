import type { HomeBannerFilters } from "@/features/home-banners/types/homeBanner.types";

export const homeBannerKeys = {
    all: [
        "home-banners",
    ] as const,

    lists: () =>
        [
            ...homeBannerKeys.all,
            "list",
        ] as const,

    list: (
        filters: HomeBannerFilters,
    ) =>
        [
            ...homeBannerKeys.lists(),
            filters,
        ] as const,

    details: () =>
        [
            ...homeBannerKeys.all,
            "detail",
        ] as const,

    detail: (
        bannerId: number,
    ) =>
        [
            ...homeBannerKeys.details(),
            bannerId,
        ] as const,

    formOptions: () =>
        [
            ...homeBannerKeys.all,
            "form-options",
        ] as const,
};