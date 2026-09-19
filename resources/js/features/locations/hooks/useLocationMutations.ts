import {
    useMutation,
    useQueryClient,
} from "@tanstack/react-query";

import {
    createLocation,
    updateLocation,
} from "@/features/locations/api/location.api";

import { locationKeys } from "@/features/locations/api/location.keys";

import type { LocationFormValues } from "@/features/locations/types/location.types";

export function useCreateLocation() {
    const queryClient =
        useQueryClient();

    return useMutation({
        mutationFn:
            createLocation,

        onSuccess: async () => {
            await queryClient.invalidateQueries(
                {
                    queryKey:
                        locationKeys.lists(),
                },
            );
        },
    });
}

export function useUpdateLocation(
    locationId: number,
) {
    const queryClient =
        useQueryClient();

    return useMutation({
        mutationFn: (
            values: LocationFormValues,
        ) =>
            updateLocation(
                locationId,
                values,
            ),

        onSuccess: async () => {
            await Promise.all([
                queryClient.invalidateQueries(
                    {
                        queryKey:
                            locationKeys.lists(),
                    },
                ),

                queryClient.invalidateQueries(
                    {
                        queryKey:
                            locationKeys.detail(
                                locationId,
                            ),
                    },
                ),
            ]);
        },
    });
}