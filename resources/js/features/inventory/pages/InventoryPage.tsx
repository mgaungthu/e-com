import { useMemo, useState } from "react";

import {
    Boxes,
    RefreshCw,
    Search,
} from "lucide-react";

import PageHeader from "@/components/PageHeader";
import { useCategories } from "@/features/categories/hooks/useCategories";
import { InventoryAdjustmentModal } from "@/features/inventory/components/InventoryAdjustmentModal";
import { InventoryHistoryModal } from "@/features/inventory/components/InventoryHistoryModal";
import { InventoryTable } from "@/features/inventory/components/InventoryTable";
import {
    INVENTORY_PER_PAGE,
    INVENTORY_STOCK_FILTERS,
} from "@/features/inventory/constants/inventory.constants";
import { useInventory } from "@/features/inventory/hooks/useInventory";
import { useAdjustInventory } from "@/features/inventory/hooks/useInventoryMutations";
import type {
    InventoryProduct,
    InventoryStockStatus,
} from "@/features/inventory/types/inventory.types";

export default function InventoryPage() {
    const [page, setPage] = useState(1);
    const [search, setSearch] = useState("");
    const [categoryId, setCategoryId] = useState("");
    const [stockStatus, setStockStatus] =
        useState<InventoryStockStatus | "all">("all");
    const [adjustmentProduct, setAdjustmentProduct] =
        useState<InventoryProduct | null>(null);
    const [historyProduct, setHistoryProduct] =
        useState<InventoryProduct | null>(null);

    const filters = useMemo(
        () => ({
            page,
            search: search.trim() || undefined,
            category_id: categoryId || undefined,
            stock_status: stockStatus,
            per_page: INVENTORY_PER_PAGE,
        }),
        [categoryId, page, search, stockStatus],
    );

    const inventoryQuery = useInventory(filters);
    const categoriesQuery = useCategories({
        page: 1,
        status: "active",
        perPage: 100,
    });
    const adjustMutation = useAdjustInventory();

    const pagination = inventoryQuery.data?.data;
    const products = pagination?.data ?? [];
    const categories = categoriesQuery.data?.data.data ?? [];

    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <PageHeader
                    title="Inventory"
                    description="Monitor stock levels, adjust quantities, and review inventory activity."
                />

                <button
                    type="button"
                    onClick={() => {
                        void inventoryQuery.refetch();
                    }}
                    disabled={inventoryQuery.isFetching}
                    className="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <RefreshCw
                        size={17}
                        className={
                            inventoryQuery.isFetching
                                ? "animate-spin"
                                : undefined
                        }
                    />
                    Refresh
                </button>
            </div>

            <div className="grid gap-4 sm:grid-cols-3">
                <div className="rounded-xl border border-slate-200 bg-white p-5">
                    <div className="flex items-center justify-between">
                        <p className="text-sm font-medium text-slate-500">
                            Total products
                        </p>
                        <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                            <Boxes size={18} />
                        </div>
                    </div>
                    <p className="mt-3 text-2xl font-bold text-slate-900">
                        {pagination?.total ?? 0}
                    </p>
                    <p className="mt-1 text-xs text-slate-500">
                        Matching current filters
                    </p>
                </div>

                <div className="rounded-xl border border-slate-200 bg-white p-5">
                    <p className="text-sm font-medium text-slate-500">
                        Units on this page
                    </p>
                    <p className="mt-3 text-2xl font-bold text-slate-900">
                        {products.reduce(
                            (total, product) =>
                                total + product.stock_quantity,
                            0,
                        )}
                    </p>
                    <p className="mt-1 text-xs text-slate-500">
                        Across {products.length} displayed products
                    </p>
                </div>

                <div className="rounded-xl border border-slate-200 bg-white p-5">
                    <p className="text-sm font-medium text-slate-500">
                        Needs attention
                    </p>
                    <p className="mt-3 text-2xl font-bold text-amber-600">
                        {
                            products.filter(
                                (product) =>
                                    product.stock_status !== "in_stock",
                            ).length
                        }
                    </p>
                    <p className="mt-1 text-xs text-slate-500">
                        Low or out of stock on this page
                    </p>
                </div>
            </div>

            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <div className="flex flex-col gap-3 border-b border-slate-200 p-4 lg:flex-row">
                    <div className="relative min-w-0 flex-1">
                        <Search
                            size={18}
                            className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                        />
                        <input
                            type="search"
                            value={search}
                            onChange={(event) => {
                                setSearch(event.target.value);
                                setPage(1);
                            }}
                            placeholder="Search product, SKU, or barcode..."
                            className="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        />
                    </div>

                    <select
                        value={categoryId}
                        onChange={(event) => {
                            setCategoryId(event.target.value);
                            setPage(1);
                        }}
                        disabled={categoriesQuery.isLoading}
                        className="min-w-48 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:cursor-not-allowed disabled:bg-slate-50"
                    >
                        <option value="">All categories</option>
                        {categories.map((category) => (
                            <option
                                key={category.id}
                                value={String(category.id)}
                            >
                                {category.name}
                            </option>
                        ))}
                    </select>

                    <select
                        value={stockStatus}
                        onChange={(event) => {
                            setStockStatus(
                                event.target
                                    .value as InventoryStockStatus | "all",
                            );
                            setPage(1);
                        }}
                        className="min-w-40 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >
                        {INVENTORY_STOCK_FILTERS.map((option) => (
                            <option
                                key={option.value}
                                value={option.value}
                            >
                                {option.label}
                            </option>
                        ))}
                    </select>
                </div>

                <InventoryTable
                    products={products}
                    pagination={pagination}
                    isLoading={inventoryQuery.isLoading}
                    isError={inventoryQuery.isError}
                    onRetry={() => {
                        void inventoryQuery.refetch();
                    }}
                    onAdjust={setAdjustmentProduct}
                    onViewHistory={setHistoryProduct}
                    onPageChange={setPage}
                />
            </div>

            <InventoryAdjustmentModal
                isOpen={adjustmentProduct !== null}
                product={adjustmentProduct}
                isSubmitting={adjustMutation.isPending}
                onClose={() => setAdjustmentProduct(null)}
                onSubmit={async (productId, values) => {
                    await adjustMutation.mutateAsync({
                        productId,
                        values,
                    });
                    setAdjustmentProduct(null);
                }}
            />

            <InventoryHistoryModal
                isOpen={historyProduct !== null}
                product={historyProduct}
                onClose={() => setHistoryProduct(null)}
            />
        </div>
    );
}
