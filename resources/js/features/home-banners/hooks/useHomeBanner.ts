import { useQuery } from "@tanstack/react-query";

import { homeBannerApi } from "@/features/home-banners/api/homeBanner.api";
import { homeBannerKeys } from "@/features/home-banners/api/homeBanner.keys";

export function useHomeBanner(
    bannerId:
        | number
        | null,
) {
    return useQuery({
        queryKey:
            homeBannerKeys.detail(
                bannerId ?? 0,
            ),

        queryFn: () =>
            homeBannerApi.show(
                bannerId!,
            ),

        enabled:
            bannerId !== null &&
            Number.isFinite(
                bannerId,
            ) &&
            bannerId > 0,
    });
}