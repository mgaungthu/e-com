import { useMemo, useState } from "react";

import axios from "axios";
import { Edit3, ImageIcon, Plus, Search, Trash2 } from "lucide-react";
import { useNavigate } from "react-router-dom";

import { useCategories } from "@/features/categories/hooks/useCategories";
import { useDeleteCategory } from "@/features/categories/hooks/useCategoryMutations";

type ErrorResponse = {
    message?: string;
};

export default function CategoriesPage() {
    const [page, setPage] = useState(1);
    const [search, setSearch] = useState("");
    const [status, setStatus] = useState<"all" | "active" | "inactive">("all");

    const navigate = useNavigate();

    const filters = useMemo(
        () => ({
            page,
            search,
            status,
            perPage: 15,
        }),
        [page, search, status],
    );

    const categoriesQuery = useCategories(filters);
    const deleteMutation = useDeleteCategory();

    const categories = categoriesQuery.data?.data.data ?? [];
    
    const pagination = categoriesQuery.data?.data;

    async function handleDelete(categoryId: number, categoryName: string) {
        const confirmed = window.confirm(`Delete "${categoryName}" category?`);

        if (!confirmed) {
            return;
        }

        try {
            await deleteMutation.mutateAsync(categoryId);
        } catch (error) {
            if (axios.isAxiosError<ErrorResponse>(error)) {
                window.alert(
                    error.response?.data.message ??
                        "Unable to delete category.",
                );

                return;
            }

            window.alert("Unable to delete category.");
        }
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-slate-900">
                        Categories
                    </h1>

                    <p className="mt-1 text-sm text-slate-500">
                        Manage product categories and hierarchy.
                    </p>
                </div>

                <button
                    type="button"
                    onClick={() => navigate("/categories/create")}
                    className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                >
                    <Plus size={18} />
                    Add category
                </button>
            </div>

            <div className="rounded-xl border border-slate-200 bg-white">
                <div className="flex flex-col gap-3 border-b border-slate-200 p-4 md:flex-row">
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
                            placeholder="Search categories..."
                            className="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        />
                    </div>

                    <select
                        value={status}
                        onChange={(event) => {
                            setStatus(
                                event.target.value as
                                    | "all"
                                    | "active"
                                    | "inactive",
                            );

                            setPage(1);
                        }}
                        className="rounded-lg border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-blue-500"
                    >
                        <option value="all">All statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                {categoriesQuery.isLoading ? (
                    <div className="p-10 text-center text-sm text-slate-500">
                        Loading categories...
                    </div>
                ) : categoriesQuery.isError ? (
                    <div className="p-10 text-center">
                        <p className="text-sm text-red-600">
                            Unable to load categories.
                        </p>

                        <button
                            type="button"
                            onClick={() => {
                                void categoriesQuery.refetch();
                            }}
                            className="mt-3 text-sm font-medium text-blue-600"
                        >
                            Try again
                        </button>
                    </div>
                ) : categories.length === 0 ? (
                    <div className="p-10 text-center">
                        <p className="text-sm font-medium text-slate-700">
                            No categories found.
                        </p>

                        <p className="mt-1 text-sm text-slate-500">
                            Create your first category to get started.
                        </p>
                    </div>
                ) : (
                    <>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200">
                                <thead className="bg-slate-50">
                                    <tr>
                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Category
                                        </th>

                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Parent
                                        </th>

                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Status
                                        </th>

                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Sort
                                        </th>

                                        <th className="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>

                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {categories.map((category) => (
                                        <tr
                                            key={category.id}
                                            className="hover:bg-slate-50"
                                        >
                                            <td className="px-5 py-4">
                                                <div className="flex items-center gap-3">
                                                    {category.image_url ? (
                                                        <img
                                                            src={
                                                                category.image_url
                                                            }
                                                            alt={category.name}
                                                            className="h-11 w-11 rounded-lg object-cover"
                                                        />
                                                    ) : (
                                                        <div className="flex h-11 w-11 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                                                            <ImageIcon
                                                                size={19}
                                                            />
                                                        </div>
                                                    )}

                                                    <div>
                                                        <p className="font-medium text-slate-900">
                                                            {category.name}
                                                        </p>

                                                        <p className="text-xs text-slate-500">
                                                            {category.slug}
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>

                                            <td className="px-5 py-4 text-sm text-slate-600">
                                                {category.parent?.name ?? "—"}
                                            </td>

                                            <td className="px-5 py-4">
                                                <span
                                                    className={[
                                                        "inline-flex rounded-full px-2.5 py-1 text-xs font-semibold",
                                                        category.is_active
                                                            ? "bg-emerald-50 text-emerald-700"
                                                            : "bg-slate-100 text-slate-600",
                                                    ].join(" ")}
                                                >
                                                    {category.is_active
                                                        ? "Active"
                                                        : "Inactive"}
                                                </span>
                                            </td>

                                            <td className="px-5 py-4 text-sm text-slate-600">
                                                {category.sort_order}
                                            </td>

                                            <td className="px-5 py-4">
                                                <div className="flex justify-end gap-2">
                                                    <button
                                                        onClick={() =>
                                                            navigate(
                                                                `/categories/${category.id}/edit`,
                                                            )
                                                        }
                                                        type="button"
                                                        className="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:bg-slate-100"
                                                        aria-label={`Edit ${category.name}`}
                                                    >
                                                        <Edit3 size={16} />
                                                    </button>

                                                    <button
                                                        type="button"
                                                        onClick={() => {
                                                            void handleDelete(
                                                                category.id,
                                                                category.name,
                                                            );
                                                        }}
                                                        disabled={
                                                            deleteMutation.isPending
                                                        }
                                                        className="rounded-lg border border-red-200 p-2 text-red-600 transition hover:bg-red-50 disabled:opacity-50"
                                                        aria-label={`Delete ${category.name}`}
                                                    >
                                                        <Trash2 size={16} />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <div className="flex items-center justify-between border-t border-slate-200 px-5 py-4">
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
                                        pagination.current_page <= 1
                                    }
                                    onClick={() => {
                                        setPage((current) =>
                                            Math.max(current - 1, 1),
                                        );
                                    }}
                                    className="rounded-lg border px-3 py-2 text-sm disabled:cursor-not-allowed disabled:opacity-50"
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
                                        setPage((current) => current + 1);
                                    }}
                                    className="rounded-lg border px-3 py-2 text-sm disabled:cursor-not-allowed disabled:opacity-50"
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
