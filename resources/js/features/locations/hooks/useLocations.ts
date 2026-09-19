import { useQuery } from "@tanstack/react-query";

import { getLocations } from "@/features/locations/api/location.api";
import { locationKeys } from "@/features/locations/api/location.keys";

import type { LocationFilters } from "@/features/locations/types/location.types";

export function useLocations(
    filters: LocationFilters,
) {
    return useQuery({
        queryKey:
            locationKeys.list(
                filters,
            ),

        queryFn: () =>
            getLocations(
                filters,
            ),
    });
}