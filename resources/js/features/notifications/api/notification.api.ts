import { api } from "@/api/client";

import type {
    NotificationFormValues,
    NotificationProductsResponse,
    SendNotificationResponse,
} from "@/features/notifications/types/notification.types";

export const notificationApi = {
    /*
    |--------------------------------------------------------------------------
    | Product Options
    |--------------------------------------------------------------------------
    */

    async products(
        search: string,
    ): Promise<NotificationProductsResponse> {
        const trimmedSearch =
            search.trim();

        const response =
            await api.get<NotificationProductsResponse>(
                "/admin/notifications/products",
                {
                    params:
                        trimmedSearch !== ""
                            ? {
                                  search: trimmedSearch,
                              }
                            : undefined,
                },
            );

        return response.data;
    },

    /*
    |--------------------------------------------------------------------------
    | Send Notification
    |--------------------------------------------------------------------------
    */

    async send(
        values: NotificationFormValues,
    ): Promise<SendNotificationResponse> {
        const productId =
            values.product_id !== ""
                ? Number(
                      values.product_id,
                  )
                : null;

        const response =
            await api.post<SendNotificationResponse>(
                "/admin/notifications/send",
                {
                    type:
                        values.type,

                    product_id:
                        productId,

                    title:
                        values.title,

                    body:
                        values.body,
                },
            );

        return response.data;
    },
};