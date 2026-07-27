import type {
    CustomerOrderFilter,
    CustomerStatus,
} from "../types/customer.types";

export const CUSTOMER_STATUS_OPTIONS: Array<{
    value: CustomerStatus | "all";
    label: string;
}> = [
    {
        value: "all",
        label: "All statuses",
    },
    {
        value: "active",
        label: "Active",
    },
    {
        value: "inactive",
        label: "Inactive",
    },
    {
        value: "blocked",
        label: "Blocked",
    },
    {
        value: "pending",
        label: "Pending",
    },
];

export const CUSTOMER_ORDER_OPTIONS: Array<{
    value: CustomerOrderFilter;
    label: string;
}> = [
    {
        value: "all",
        label: "All customers",
    },
    {
        value: "has_orders",
        label: "Has orders",
    },
    {
        value: "no_orders",
        label: "No orders",
    },
];

export const CUSTOMER_PAGE_SIZE = 15;