import {
    useEffect,
    useMemo,
    useState,
    type FormEvent,
} from "react";

import axios from "axios";
import {
    ArrowDown,
    ArrowUp,
    Equal,
    X,
} from "lucide-react";

import {
    FormErrorAlert,
    FormField,
    NumberInput,
    SelectInput,
    TextArea,
    TextInput,
} from "@/components/forms";
import {
    INVENTORY_ADJUSTMENT_INITIAL_VALUES,
    INVENTORY_ADJUSTMENT_TYPES,
} from "@/features/inventory/constants/inventory.constants";
import type {
    InventoryAdjustmentValues,
    InventoryErrorResponse,
    InventoryProduct,
    InventoryTransactionType,
    InventoryValidationErrors,
} from "@/features/inventory/types/inventory.types";

type InventoryAdjustmentModalProps = {
    isOpen: boolean;
    product: InventoryProduct | null;
    isSubmitting: boolean;
    onClose: () => void;
    onSubmit: (
        productId: number,
        values: InventoryAdjustmentValues,
    ) => Promise<void>;
};

const adjustmentIcons: Record<
    InventoryTransactionType,
    typeof ArrowUp
> = {
    addition: ArrowUp,
    subtraction: ArrowDown,
    set: Equal,
};

export function InventoryAdjustmentModal({
    isOpen,
    product,
    isSubmitting,
    onClose,
    onSubmit,
}: InventoryAdjustmentModalProps) {
    const [values, setValues] =
        useState<InventoryAdjustmentValues>({
            ...INVENTORY_ADJUSTMENT_INITIAL_VALUES,
        });
    const [errors, setErrors] =
        useState<InventoryValidationErrors>({});
    const [formError, setFormError] =
        useState<string | null>(null);

    useEffect(() => {
        if (!isOpen) {
            return;
        }

        setValues({
            ...INVENTORY_ADJUSTMENT_INITIAL_VALUES,
        });
        setErrors({});
        setFormError(null);
    }, [isOpen, product?.id]);

    useEffect(() => {
        if (!isOpen) {
            return;
        }

        function handleKeyDown(event: KeyboardEvent) {
            if (event.key === "Escape" && !isSubmitting) {
                onClose();
            }
        }

        window.addEventListener("keydown", handleKeyDown);
        return () => {
            window.removeEventListener("keydown", handleKeyDown);
        };
    }, [isOpen, isSubmitting, onClose]);

    const resultingQuantity = useMemo(() => {
        if (!product) {
            return 0;
        }

        switch (values.type) {
            case "addition":
                return product.stock_quantity + values.quantity;
            case "subtraction":
                return product.stock_quantity - values.quantity;
            case "set":
                return values.quantity;
        }
    }, [product, values.quantity, values.type]);

    function updateValue<Key extends keyof InventoryAdjustmentValues>(
        key: Key,
        value: InventoryAdjustmentValues[Key],
    ) {
        setValues((current) => ({
            ...current,
            [key]: value,
        }));
        setErrors((current) => {
            if (!current[key]) {
                return current;
            }

            const next = { ...current };
            delete next[key];
            return next;
        });
        setFormError(null);
    }

    async function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (!product) {
            return;
        }

        setErrors({});
        setFormError(null);

        try {
            await onSubmit(product.id, values);
        } catch (error) {
            if (axios.isAxiosError<InventoryErrorResponse>(error)) {
                setErrors(error.response?.data.errors ?? {});
                setFormError(
                    error.response?.data.message ??
                        "Unable to update inventory.",
                );
                return;
            }

            setFormError("An unexpected error occurred.");
        }
    }

    if (!isOpen || !product) {
        return null;
    }

    const SelectedIcon = adjustmentIcons[values.type];

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-[1px]"
            role="presentation"
            onMouseDown={(event) => {
                if (
                    event.target === event.currentTarget &&
                    !isSubmitting
                ) {
                    onClose();
                }
            }}
        >
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="inventory-adjustment-title"
                className="max-h-[calc(100vh-2rem)] w-full max-w-xl overflow-y-auto rounded-2xl bg-white shadow-2xl"
            >
                <div className="flex items-start justify-between border-b border-slate-200 px-6 py-5">
                    <div>
                        <h2
                            id="inventory-adjustment-title"
                            className="text-lg font-bold text-slate-900"
                        >
                            Adjust inventory
                        </h2>
                        <p className="mt-1 text-sm text-slate-500">
                            {product.name} · {product.sku}
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        disabled={isSubmitting}
                        className="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                        aria-label="Close adjustment modal"
                    >
                        <X size={20} />
                    </button>
                </div>

                <form onSubmit={handleSubmit} noValidate>
                    <div className="space-y-5 p-6">
                        <FormErrorAlert message={formError} />

                        <div className="grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-4">
                            <div>
                                <p className="text-xs font-medium uppercase tracking-wide text-slate-500">
                                    Current stock
                                </p>
                                <p className="mt-1 text-2xl font-bold text-slate-900">
                                    {product.stock_quantity}
                                </p>
                            </div>
                            <div>
                                <p className="text-xs font-medium uppercase tracking-wide text-slate-500">
                                    New stock
                                </p>
                                <p
                                    className={[
                                        "mt-1 text-2xl font-bold",
                                        resultingQuantity < 0
                                            ? "text-red-600"
                                            : "text-blue-600",
                                    ].join(" ")}
                                >
                                    {resultingQuantity}
                                </p>
                            </div>
                        </div>

                        <FormField
                            label="Adjustment type"
                            htmlFor="inventory-adjustment-type"
                            required
                            error={errors.type?.[0]}
                        >
                            <SelectInput
                                id="inventory-adjustment-type"
                                value={values.type}
                                disabled={isSubmitting}
                                error={errors.type?.[0]}
                                onChange={(event) =>
                                    updateValue(
                                        "type",
                                        event.target
                                            .value as InventoryTransactionType,
                                    )
                                }
                            >
                                {INVENTORY_ADJUSTMENT_TYPES.map(
                                    (option) => (
                                        <option
                                            key={option.value}
                                            value={option.value}
                                        >
                                            {option.label} —{" "}
                                            {option.description}
                                        </option>
                                    ),
                                )}
                            </SelectInput>
                        </FormField>

                        <FormField
                            label={
                                values.type === "set"
                                    ? "New quantity"
                                    : "Quantity"
                            }
                            htmlFor="inventory-quantity"
                            required
                            helperText={
                                values.type === "subtraction"
                                    ? `Maximum removable quantity: ${product.stock_quantity}`
                                    : undefined
                            }
                            error={errors.quantity?.[0]}
                        >
                            <NumberInput
                                id="inventory-quantity"
                                min={0}
                                step={1}
                                value={values.quantity}
                                disabled={isSubmitting}
                                error={errors.quantity?.[0]}
                                onChange={(event) =>
                                    updateValue(
                                        "quantity",
                                        Number(
                                            event.target.value || 0,
                                        ),
                                    )
                                }
                            />
                        </FormField>

                        <FormField
                            label="Reason"
                            htmlFor="inventory-reason"
                            helperText="Optional short explanation for this adjustment."
                            error={errors.reason?.[0]}
                        >
                            <TextInput
                                id="inventory-reason"
                                value={values.reason}
                                maxLength={255}
                                disabled={isSubmitting}
                                placeholder="e.g. New shipment received"
                                error={errors.reason?.[0]}
                                onChange={(event) =>
                                    updateValue(
                                        "reason",
                                        event.target.value,
                                    )
                                }
                            />
                        </FormField>

                        <FormField
                            label="Note"
                            htmlFor="inventory-note"
                            error={errors.note?.[0]}
                        >
                            <TextArea
                                id="inventory-note"
                                rows={3}
                                value={values.note}
                                maxLength={1000}
                                disabled={isSubmitting}
                                placeholder="Add internal details if needed..."
                                error={errors.note?.[0]}
                                onChange={(event) =>
                                    updateValue(
                                        "note",
                                        event.target.value,
                                    )
                                }
                            />
                        </FormField>
                    </div>

                    <div className="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            onClick={onClose}
                            disabled={isSubmitting}
                            className="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={
                                isSubmitting ||
                                resultingQuantity < 0
                            }
                            className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <SelectedIcon size={17} />
                            {isSubmitting
                                ? "Updating..."
                                : "Update inventory"}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
