import { useMutation } from "@tanstack/react-query";

import { notificationApi } from "@/features/notifications/api/notification.api";

import type { NotificationFormValues } from "@/features/notifications/types/notification.types";

export function useSendNotification() {
    return useMutation({
        mutationFn: (
            values: NotificationFormValues,
        ) =>
            notificationApi.send(
                values,
            ),
    });
}