import { useQuery } from "@tanstack/react-query";

import { getLocation } from "@/features/locations/api/location.api";
import { locationKeys } from "@/features/locations/api/location.keys";

export function useLocation(
    locationId:
        | number
        | null,
) {
    return useQuery({
        queryKey:
            locationKeys.detail(
                locationId ?? 0,
            ),

        queryFn: () =>
            getLocation(
                locationId as number,
            ),

        enabled:
            locationId !== null,
    });
}