import type {
    InventoryAdjustmentValues,
    InventoryStockStatus,
    InventoryTransactionType,
} from "@/features/inventory/types/inventory.types";

export const INVENTORY_PER_PAGE = 15;

export const INVENTORY_STOCK_FILTERS: Array<{
    value: InventoryStockStatus | "all";
    label: string;
}> = [
    { value: "all", label: "All stock" },
    { value: "in_stock", label: "In stock" },
    { value: "low_stock", label: "Low stock" },
    { value: "out_of_stock", label: "Out of stock" },
];

export const INVENTORY_ADJUSTMENT_TYPES: Array<{
    value: InventoryTransactionType;
    label: string;
    description: string;
}> = [
    {
        value: "addition",
        label: "Add stock",
        description: "Increase the current quantity.",
    },
    {
        value: "subtraction",
        label: "Remove stock",
        description: "Decrease the current quantity.",
    },
    {
        value: "set",
        label: "Set quantity",
        description: "Replace stock with an exact quantity.",
    },
];

export const INVENTORY_ADJUSTMENT_INITIAL_VALUES: InventoryAdjustmentValues = {
    type: "addition",
    quantity: 1,
    reason: "",
    note: "",
};
