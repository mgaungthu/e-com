import type {
    NotificationProductOption,
    NotificationType,
} from "@/features/notifications/types/notification.types";

export const NOTIFICATION_TYPE_OPTIONS: Array<{
    value: NotificationType;
    label: string;
}> = [
    {
        value: "new_product",
        label: "New Product",
    },
    {
        value: "product_restocked",
        label: "Restock",
    },
    {
        value: "general",
        label: "General",
    },
];

export function notificationRequiresProduct(
    type: NotificationType,
): boolean {
    return (
        type === "new_product" ||
        type === "product_restocked"
    );
}

export function buildNotificationTemplate(
    type: NotificationType,
    product?: NotificationProductOption | null,
): {
    title: string;
    body: string;
} {
    switch (type) {
        case "new_product":
            return {
                title: "New Product Arrived",
                body: product
                    ? `${product.name} is now available at Burmese Shave Club.`
                    : "",
            };

        case "product_restocked":
            return {
                title: "Back in Stock",
                body: product
                    ? `${product.name} is back in stock. Shop now before it sells out again.`
                    : "",
            };

        case "general":
        default:
            return {
                title: "",
                body: "",
            };
    }
}