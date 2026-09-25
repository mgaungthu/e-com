import { useQuery } from "@tanstack/react-query";

import { notificationApi } from "@/features/notifications/api/notification.api";
import { notificationKeys } from "@/features/notifications/api/notification.keys";

export function useNotificationProducts(
    search: string,
    enabled = true,
) {
    return useQuery({
        queryKey:
            notificationKeys.products(
                search,
            ),

        queryFn: () =>
            notificationApi.products(
                search,
            ),

        enabled,

        staleTime: 60_000,
    });
}