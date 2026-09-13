import { useQuery } from "@tanstack/react-query";

import { homeBannerApi } from "@/features/home-banners/api/homeBanner.api";
import { homeBannerKeys } from "@/features/home-banners/api/homeBanner.keys";

import type { HomeBannerFilters } from "@/features/home-banners/types/homeBanner.types";

export function useHomeBanners(
    filters: HomeBannerFilters,
) {
    return useQuery({
        queryKey:
            homeBannerKeys.list(
                filters,
            ),

        queryFn: () =>
            homeBannerApi.list(
                filters,
            ),
    });
}

export function useHomeBannerFormOptions() {
    return useQuery({
        queryKey:
            homeBannerKeys.formOptions(),

        queryFn: async () => {
            const products =
                await homeBannerApi.productOptions();

            return {
                products,
            };
        },

        staleTime: 60_000,
    });
}