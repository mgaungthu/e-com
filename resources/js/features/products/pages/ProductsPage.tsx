import { useMemo, useState } from "react";

import axios from "axios";
import {
    Edit3,
    ImageIcon,
    Package,
    Plus,
    Search,
    Trash2,
} from "lucide-react";
import { useNavigate } from "react-router-dom";

import { useDeleteProduct } from "@/features/products/hooks/useProductMutations";
import { useProducts } from "@/features/products/hooks/useProducts";

type ProductStatus = "all" | "active" | "inactive";

type StockStatus =
    | "all"
    | "in_stock"
    | "low_stock"
    | "out_of_stock";

type ErrorResponse = {
    message?: string;
};

function formatCurrency(value: number | string | null | undefined) {
    const numericValue = Number(value ?? 0);

    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number.isNaN(numericValue) ? 0 : numericValue);
}

function formatStockStatus(stockStatus: string) {
    switch (stockStatus) {
        case "in_stock":
            return "In stock";

        case "low_stock":
            return "Low stock";

        case "out_of_stock":
            return "Out of stock";

        default:
            return stockStatus;
    }
}

function getStockStatusClassName(stockStatus: string) {
    switch (stockStatus) {
        case "in_stock":
            return "bg-emerald-50 text-emerald-700";

        case "low_stock":
            return "bg-amber-50 text-amber-700";

        case "out_of_stock":
            return "bg-red-50 text-red-700";

        default:
            return "bg-slate-100 text-slate-600";
    }
}

export default function ProductsPage() {
    const [page, setPage] = useState(1);
    const [search, setSearch] = useState("");
    const [status, setStatus] =
        useState<ProductStatus>("all");
    const [stockStatus, setStockStatus] =
        useState<StockStatus>("all");

    const navigate = useNavigate();

    const filters = useMemo(
        () => ({
            page,
            search: search || undefined,
            status,
            stock_status: stockStatus,
            per_page: 15,
        }),
        [page, search, status, stockStatus],
    );

    const productsQuery = useProducts(filters);
    const deleteMutation = useDeleteProduct();

    const pagination = productsQuery.data?.data;
    const products = pagination?.data ?? [];

    async function handleDelete(
        productId: number,
        productName: string,
    ) {
        const confirmed = window.confirm(
            `Delete "${productName}" product?`,
        );

        if (!confirmed) {
            return;
        }

        try {
            await deleteMutation.mutateAsync(productId);
        } catch (error) {
            if (axios.isAxiosError<ErrorResponse>(error)) {
                window.alert(
                    error.response?.data.message ??
                        "Unable to delete product.",
                );

                return;
            }

            window.alert("Unable to delete product.");
        }
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-slate-900">
                        Products
                    </h1>

                    <p className="mt-1 text-sm text-slate-500">
                        Manage products, pricing, categories, and
                        stock.
                    </p>
                </div>

                <button
                    type="button"
                    onClick={() => navigate("/products/create")}
                    className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                >
                    <Plus size={18} />
                    Add product
                </button>
            </div>

            <div className="rounded-xl border border-slate-200 bg-white">
                <div className="flex flex-col gap-3 border-b border-slate-200 p-4 lg:flex-row">
                    <div className="relative flex-1">
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
                            placeholder="Search name, SKU, or barcode..."
                            className="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        />
                    </div>

                    <select
                        value={status}
                        onChange={(event) => {
                            setStatus(
                                event.target
                                    .value as ProductStatus,
                            );

                            setPage(1);
                        }}
                        className="rounded-lg border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="all">
                            All statuses
                        </option>

                        <option value="active">
                            Active
                        </option>

                        <option value="inactive">
                            Inactive
                        </option>
                    </select>

                    <select
                        value={stockStatus}
                        onChange={(event) => {
                            setStockStatus(
                                event.target
                                    .value as StockStatus,
                            );

                            setPage(1);
                        }}
                        className="rounded-lg border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="all">
                            All stock
                        </option>

                        <option value="in_stock">
                            In stock
                        </option>

                        <option value="low_stock">
                            Low stock
                        </option>

                        <option value="out_of_stock">
                            Out of stock
                        </option>
                    </select>
                </div>

                {productsQuery.isLoading ? (
                    <div className="p-10 text-center text-sm text-slate-500">
                        Loading products...
                    </div>
                ) : productsQuery.isError ? (
                    <div className="p-10 text-center">
                        <p className="text-sm text-red-600">
                            Unable to load products.
                        </p>

                        <button
                            type="button"
                            onClick={() => {
                                void productsQuery.refetch();
                            }}
                            className="mt-3 text-sm font-medium text-blue-600 transition hover:text-blue-700"
                        >
                            Try again
                        </button>
                    </div>
                ) : products.length === 0 ? (
                    <div className="p-10 text-center">
                        <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <Package size={22} />
                        </div>

                        <p className="mt-4 text-sm font-medium text-slate-700">
                            No products found.
                        </p>

                        <p className="mt-1 text-sm text-slate-500">
                            Create your first product to get
                            started.
                        </p>
                    </div>
                ) : (
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
                                            Price
                                        </th>

                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Stock
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
                                    {products.map((product) => {
                                        const displayPrice =
                                            product.sale_price ??
                                            product.price;

                                        return (
                                            <tr
                                                key={product.id}
                                                className="transition hover:bg-slate-50"
                                            >
                                                <td className="px-5 py-4">
                                                    <div className="flex items-center gap-3">
                                                        {product.image_url ? (
                                                            <img
                                                                src={
                                                                    product.image_url
                                                                }
                                                                alt={
                                                                    product.name
                                                                }
                                                                className="h-11 w-11 rounded-lg object-cover"
                                                            />
                                                        ) : (
                                                            <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                                                                <ImageIcon
                                                                    size={
                                                                        19
                                                                    }
                                                                />
                                                            </div>
                                                        )}

                                                        <div className="min-w-0">
                                                            <p className="max-w-xs truncate font-medium text-slate-900">
                                                                {
                                                                    product.name
                                                                }
                                                            </p>

                                                            <p className="mt-0.5 text-xs text-slate-500">
                                                                {product
                                                                    .category
                                                                    ?.name ??
                                                                    "No category"}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td className="px-5 py-4 text-sm text-slate-600">
                                                    {product.sku ||
                                                        "—"}
                                                </td>

                                                <td className="px-5 py-4">
                                                    <div className="text-sm font-semibold text-slate-900">
                                                        {formatCurrency(
                                                            displayPrice,
                                                        )}
                                                    </div>

                                                    {product.sale_price !=
                                                        null &&
                                                        Number(
                                                            product.sale_price,
                                                        ) <
                                                            Number(
                                                                product.price,
                                                            ) && (
                                                            <div className="mt-0.5 text-xs text-slate-400 line-through">
                                                                {formatCurrency(
                                                                    product.price,
                                                                )}
                                                            </div>
                                                        )}
                                                </td>

                                                <td className="px-5 py-4">
                                                    <p className="text-sm font-medium text-slate-900">
                                                        {
                                                            product.stock_quantity
                                                        }
                                                    </p>

                                                    <span
                                                        className={[
                                                            "mt-1 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold",
                                                            getStockStatusClassName(
                                                                product.stock_status,
                                                            ),
                                                        ].join(
                                                            " ",
                                                        )}
                                                    >
                                                        {formatStockStatus(
                                                            product.stock_status,
                                                        )}
                                                    </span>
                                                </td>

                                                <td className="px-5 py-4">
                                                    <span
                                                        className={[
                                                            "inline-flex rounded-full px-2.5 py-1 text-xs font-semibold",
                                                            product.is_active
                                                                ? "bg-emerald-50 text-emerald-700"
                                                                : "bg-slate-100 text-slate-600",
                                                        ].join(
                                                            " ",
                                                        )}
                                                    >
                                                        {product.is_active
                                                            ? "Active"
                                                            : "Inactive"}
                                                    </span>
                                                </td>

                                                <td className="px-5 py-4">
                                                    <div className="flex justify-end gap-2">
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                navigate(
                                                                    `/products/${product.id}/edit`,
                                                                )
                                                            }
                                                            className="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:bg-slate-100"
                                                            aria-label={`Edit ${product.name}`}
                                                        >
                                                            <Edit3
                                                                size={
                                                                    16
                                                                }
                                                            />
                                                        </button>

                                                        <button
                                                            type="button"
                                                            disabled={
                                                                deleteMutation.isPending
                                                            }
                                                            onClick={() => {
                                                                void handleDelete(
                                                                    product.id,
                                                                    product.name,
                                                                );
                                                            }}
                                                            className="rounded-lg border border-red-200 p-2 text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                                                            aria-label={`Delete ${product.name}`}
                                                        >
                                                            <Trash2
                                                                size={
                                                                    16
                                                                }
                                                            />
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>

                        <div className="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <p className="text-sm text-slate-500">
                                Showing {pagination?.from ?? 0}–
                                {pagination?.to ?? 0} of{" "}
                                {pagination?.total ?? 0}
                            </p>

                            <div className="flex gap-2">
                                <button
                                    type="button"
                                    disabled={
                                        !pagination ||
                                        pagination.current_page <=
                                            1
                                    }
                                    onClick={() => {
                                        setPage((current) =>
                                            Math.max(
                                                current - 1,
                                                1,
                                            ),
                                        );
                                    }}
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
                                    onClick={() => {
                                        setPage(
                                            (current) =>
                                                current + 1,
                                        );
                                    }}
                                    className="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
}
