import {
    useMutation,
    useQueryClient,
} from "@tanstack/react-query";

import { homeBannerApi } from "@/features/home-banners/api/homeBanner.api";
import { homeBannerKeys } from "@/features/home-banners/api/homeBanner.keys";

import type { HomeBannerFormValues } from "@/features/home-banners/types/homeBanner.types";

export function useCreateHomeBanner() {
    const queryClient =
        useQueryClient();

    return useMutation({
        mutationFn: (
            values: HomeBannerFormValues,
        ) =>
            homeBannerApi.create(
                values,
            ),

        onSuccess: async () => {
            await queryClient.invalidateQueries({
                queryKey:
                    homeBannerKeys.lists(),
            });
        },
    });
}

export function useUpdateHomeBanner(
    bannerId: number,
) {
    const queryClient =
        useQueryClient();

    return useMutation({
        mutationFn: (
            values: HomeBannerFormValues,
        ) =>
            homeBannerApi.update(
                bannerId,
                values,
            ),

        onSuccess: async () => {
            await Promise.all([
                queryClient.invalidateQueries({
                    queryKey:
                        homeBannerKeys.lists(),
                }),

                queryClient.invalidateQueries({
                    queryKey:
                        homeBannerKeys.detail(
                            bannerId,
                        ),
                }),
            ]);
        },
    });
}

export function useDeleteHomeBanner() {
    const queryClient =
        useQueryClient();

    return useMutation({
        mutationFn: (
            bannerId: number,
        ) =>
            homeBannerApi.remove(
                bannerId,
            ),

        onSuccess: async () => {
            await queryClient.invalidateQueries({
                queryKey:
                    homeBannerKeys.lists(),
            });
        },
    });
}