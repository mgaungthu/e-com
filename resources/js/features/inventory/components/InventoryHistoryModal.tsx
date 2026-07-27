import { useEffect, useState } from "react";

import {
    ArrowDown,
    ArrowUp,
    Clock3,
    Equal,
    X,
} from "lucide-react";

import { useInventoryHistory } from "@/features/inventory/hooks/useInventoryHistory";
import type {
    InventoryProduct,
    InventoryTransaction,
} from "@/features/inventory/types/inventory.types";

type InventoryHistoryModalProps = {
    isOpen: boolean;
    product: InventoryProduct | null;
    onClose: () => void;
};

function formatDate(value: string): string {
    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat("en-US", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(date);
}

function getTransactionDisplay(transaction: InventoryTransaction) {
    switch (transaction.type) {
        case "addition":
            return {
                icon: ArrowUp,
                label: "Stock added",
                quantity: `+${Math.abs(transaction.quantity)}`,
                className: "bg-emerald-50 text-emerald-700",
            };
        case "subtraction":
            return {
                icon: ArrowDown,
                label: "Stock removed",
                quantity: `-${Math.abs(transaction.quantity)}`,
                className: "bg-red-50 text-red-700",
            };
        case "set":
            return {
                icon: Equal,
                label: "Quantity set",
                quantity: `${transaction.quantity_after}`,
                className: "bg-blue-50 text-blue-700",
            };
    }
}

export function InventoryHistoryModal({
    isOpen,
    product,
    onClose,
}: InventoryHistoryModalProps) {
    const [page, setPage] = useState(1);

    useEffect(() => {
        if (isOpen) {
            setPage(1);
        }
    }, [isOpen, product?.id]);

    useEffect(() => {
        if (!isOpen) {
            return;
        }

        function handleKeyDown(event: KeyboardEvent) {
            if (event.key === "Escape") {
                onClose();
            }
        }

        window.addEventListener("keydown", handleKeyDown);
        return () => {
            window.removeEventListener("keydown", handleKeyDown);
        };
    }, [isOpen, onClose]);

    const historyQuery = useInventoryHistory(
        product?.id ?? null,
        page,
        isOpen,
    );

    if (!isOpen || !product) {
        return null;
    }

    const pagination = historyQuery.data?.data;
    const transactions = pagination?.data ?? [];

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-[1px]"
            role="presentation"
            onMouseDown={(event) => {
                if (event.target === event.currentTarget) {
                    onClose();
                }
            }}
        >
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="inventory-history-title"
                className="flex max-h-[calc(100vh-2rem)] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
            >
                <div className="flex items-start justify-between border-b border-slate-200 px-6 py-5">
                    <div>
                        <h2
                            id="inventory-history-title"
                            className="text-lg font-bold text-slate-900"
                        >
                            Inventory history
                        </h2>
                        <p className="mt-1 text-sm text-slate-500">
                            {product.name} · Current stock:{" "}
                            {product.stock_quantity}
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        aria-label="Close inventory history"
                    >
                        <X size={20} />
                    </button>
                </div>

                <div className="min-h-0 flex-1 overflow-y-auto">
                    {historyQuery.isLoading ? (
                        <div className="p-12 text-center text-sm text-slate-500">
                            Loading history...
                        </div>
                    ) : historyQuery.isError ? (
                        <div className="p-12 text-center">
                            <p className="text-sm font-medium text-red-600">
                                Unable to load inventory history.
                            </p>
                            <button
                                type="button"
                                onClick={() => {
                                    void historyQuery.refetch();
                                }}
                                className="mt-3 text-sm font-semibold text-blue-600"
                            >
                                Try again
                            </button>
                        </div>
                    ) : transactions.length === 0 ? (
                        <div className="p-12 text-center">
                            <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                <Clock3 size={22} />
                            </div>
                            <p className="mt-4 text-sm font-semibold text-slate-700">
                                No adjustments yet.
                            </p>
                            <p className="mt-1 text-sm text-slate-500">
                                Inventory changes will appear here.
                            </p>
                        </div>
                    ) : (
                        <div className="divide-y divide-slate-100">
                            {transactions.map((transaction) => {
                                const display =
                                    getTransactionDisplay(transaction);
                                const Icon = display.icon;

                                return (
                                    <div
                                        key={transaction.id}
                                        className="flex gap-4 px-6 py-4"
                                    >
                                        <div
                                            className={[
                                                "flex h-10 w-10 shrink-0 items-center justify-center rounded-full",
                                                display.className,
                                            ].join(" ")}
                                        >
                                            <Icon size={18} />
                                        </div>

                                        <div className="min-w-0 flex-1">
                                            <div className="flex flex-wrap items-start justify-between gap-2">
                                                <div>
                                                    <p className="text-sm font-semibold text-slate-900">
                                                        {display.label}
                                                    </p>
                                                    <p className="mt-0.5 text-xs text-slate-500">
                                                        {
                                                            transaction
                                                                .quantity_before
                                                        }{" "}
                                                        →{" "}
                                                        {
                                                            transaction
                                                                .quantity_after
                                                        }{" "}
                                                        units
                                                    </p>
                                                </div>
                                                <span
                                                    className={[
                                                        "rounded-full px-2.5 py-1 text-xs font-bold",
                                                        display.className,
                                                    ].join(" ")}
                                                >
                                                    {display.quantity}
                                                </span>
                                            </div>

                                            {transaction.reason ? (
                                                <p className="mt-2 text-sm text-slate-700">
                                                    {transaction.reason}
                                                </p>
                                            ) : null}
                                            {transaction.note ? (
                                                <p className="mt-1 text-xs leading-5 text-slate-500">
                                                    {transaction.note}
                                                </p>
                                            ) : null}

                                            <p className="mt-2 text-xs text-slate-400">
                                                {formatDate(
                                                    transaction.created_at,
                                                )}
                                                {" · "}
                                                {transaction.user?.name ??
                                                    "System"}
                                            </p>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>

                {pagination && pagination.last_page > 1 ? (
                    <div className="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-6 py-4">
                        <p className="text-sm text-slate-500">
                            Page {pagination.current_page} of{" "}
                            {pagination.last_page}
                        </p>
                        <div className="flex gap-2">
                            <button
                                type="button"
                                disabled={pagination.current_page <= 1}
                                onClick={() =>
                                    setPage((current) =>
                                        Math.max(current - 1, 1),
                                    )
                                }
                                className="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Previous
                            </button>
                            <button
                                type="button"
                                disabled={
                                    pagination.current_page >=
                                    pagination.last_page
                                }
                                onClick={() =>
                                    setPage(
                                        (current) => current + 1,
                                    )
                                }
                                className="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                ) : null}
            </div>
        </div>
    );
}
