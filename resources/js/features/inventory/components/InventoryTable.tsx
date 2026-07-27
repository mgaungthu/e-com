import {
    History,
    ImageIcon,
    Package,
    SlidersHorizontal,
} from "lucide-react";

import { InventoryStatusBadge } from "@/features/inventory/components/InventoryStatusBadge";
import type {
    InventoryPagination,
    InventoryProduct,
} from "@/features/inventory/types/inventory.types";

type InventoryTableProps = {
    products: InventoryProduct[];
    pagination?: InventoryPagination<InventoryProduct>;
    isLoading: boolean;
    isError: boolean;
    onRetry: () => void;
    onAdjust: (product: InventoryProduct) => void;
    onViewHistory: (product: InventoryProduct) => void;
    onPageChange: (page: number) => void;
};

export function InventoryTable({
    products,
    pagination,
    isLoading,
    isError,
    onRetry,
    onAdjust,
    onViewHistory,
    onPageChange,
}: InventoryTableProps) {
    if (isLoading) {
        return (
            <div className="p-12 text-center text-sm text-slate-500">
                Loading inventory...
            </div>
        );
    }

    if (isError) {
        return (
            <div className="p-12 text-center">
                <p className="text-sm font-medium text-red-600">
                    Unable to load inventory.
                </p>
                <button
                    type="button"
                    onClick={onRetry}
                    className="mt-3 text-sm font-semibold text-blue-600 transition hover:text-blue-700"
                >
                    Try again
                </button>
            </div>
        );
    }

    if (products.length === 0) {
        return (
            <div className="p-12 text-center">
                <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <Package size={22} />
                </div>
                <p className="mt-4 text-sm font-semibold text-slate-700">
                    No inventory items found.
                </p>
                <p className="mt-1 text-sm text-slate-500">
                    Try changing the search or filters.
                </p>
            </div>
        );
    }

    return (
        <>
            <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-slate-200">
                    <thead className="bg-slate-50">
                        <tr>
                            <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Product
                            </th>
                            <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                SKU
                            </th>
                            <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Available
                            </th>
                            <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Low stock at
                            </th>
                            <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Status
                            </th>
                            <th className="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody className="divide-y divide-slate-100 bg-white">
                        {products.map((product) => (
                            <tr
                                key={product.id}
                                className="transition hover:bg-slate-50"
                            >
                                <td className="px-5 py-4">
                                    <div className="flex items-center gap-3">
                                        {product.image_url ? (
                                            <img
                                                src={product.image_url}
                                                alt={product.name}
                                                className="h-11 w-11 rounded-lg object-cover"
                                            />
                                        ) : (
                                            <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                                                <ImageIcon size={19} />
                                            </div>
                                        )}

                                        <div className="min-w-0">
                                            <p className="max-w-xs truncate text-sm font-semibold text-slate-900">
                                                {product.name}
                                            </p>
                                            <p className="mt-0.5 text-xs text-slate-500">
                                                {product.category?.name ??
                                                    "No category"}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td className="px-5 py-4 text-sm font-medium text-slate-600">
                                    {product.sku || "—"}
                                </td>

                                <td className="px-5 py-4">
                                    <span className="text-lg font-bold text-slate-900">
                                        {product.stock_quantity}
                                    </span>
                                    <span className="ml-1 text-xs text-slate-500">
                                        units
                                    </span>
                                </td>

                                <td className="px-5 py-4 text-sm text-slate-600">
                                    {product.low_stock_threshold} units
                                </td>

                                <td className="px-5 py-4">
                                    <InventoryStatusBadge
                                        status={product.stock_status}
                                    />
                                </td>

                                <td className="px-5 py-4">
                                    <div className="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            onClick={() =>
                                                onViewHistory(product)
                                            }
                                            className="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                                            aria-label={`View inventory history for ${product.name}`}
                                        >
                                            <History size={15} />
                                            History
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => onAdjust(product)}
                                            className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-700"
                                            aria-label={`Adjust inventory for ${product.name}`}
                                        >
                                            <SlidersHorizontal size={15} />
                                            Adjust
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <div className="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p className="text-sm text-slate-500">
                    Showing {pagination?.from ?? 0}–
                    {pagination?.to ?? 0} of {pagination?.total ?? 0}
                </p>

                <div className="flex gap-2">
                    <button
                        type="button"
                        disabled={
                            !pagination ||
                            pagination.current_page <= 1
                        }
                        onClick={() =>
                            onPageChange(
                                Math.max(
                                    (pagination?.current_page ?? 1) - 1,
                                    1,
                                ),
                            )
                        }
                        className="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Previous
                    </button>
                    <button
                        type="button"
                        disabled={
                            !pagination ||
                            pagination.current_page >=
                                pagination.last_page
                        }
                        onClick={() =>
                            onPageChange(
                                (pagination?.current_page ?? 1) + 1,
                            )
                        }
                        className="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Next
                    </button>
                </div>
            </div>
        </>
    );
}
