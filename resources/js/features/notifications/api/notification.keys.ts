export const notificationKeys = {
    all: [
        "notifications",
    ] as const,

    products: (
        search: string,
    ) =>
        [
            ...notificationKeys.all,
            "products",
            search,
        ] as const,
};