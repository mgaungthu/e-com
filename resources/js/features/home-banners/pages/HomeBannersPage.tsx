import {
    Edit3,
    Image as ImageIcon,
    Plus,
    Search,
    Trash2,
} from "lucide-react";

import {
    useMemo,
    useState,
} from "react";

import {
    useNavigate,
} from "react-router-dom";

import { useDeleteHomeBanner } from "@/features/home-banners/hooks/useHomeBannerMutations";
import { useHomeBanners } from "@/features/home-banners/hooks/useHomeBanners";

import { useAuthStore } from "@/store/authStore";

type StatusFilter =
    | "all"
    | "active"
    | "inactive";

function formatDate(
    value: string | null,
): string {
    if (!value) {
        return "—";
    }

    const date =
        new Date(value);

    if (
        Number.isNaN(
            date.getTime(),
        )
    ) {
        return "—";
    }

    return new Intl.DateTimeFormat(
        undefined,
        {
            dateStyle: "medium",
            timeStyle: "short",
        },
    ).format(date);
}

export default function HomeBannersPage() {
    const navigate =
        useNavigate();

    const user =
        useAuthStore(
            (state) =>
                state.user,
        );

    const [
        page,
        setPage,
    ] = useState(1);

    const [
        search,
        setSearch,
    ] = useState("");

    const [
        status,
        setStatus,
    ] =
        useState<StatusFilter>(
            "all",
        );

    const filters =
        useMemo(
            () => ({
                page,

                per_page: 15,

                search:
                    search.trim() ||
                    undefined,

                is_active:
                    status ===
                    "all"
                        ? undefined
                        : status ===
                          "active",
            }),
            [
                page,
                search,
                status,
            ],
        );

    const bannersQuery =
        useHomeBanners(
            filters,
        );

    const deleteMutation =
        useDeleteHomeBanner();

    const permissions =
        user?.permissions ?? [];

    const canCreate =
        permissions.includes(
            "home_banners.create",
        );

    const canUpdate =
        permissions.includes(
            "home_banners.update",
        );

    const canDelete =
        permissions.includes(
            "home_banners.delete",
        );

    const pagination =
        bannersQuery.data
            ?.data;

    const banners =
        pagination?.data ?? [];

    async function handleDelete(
        bannerId: number,
        productName: string,
    ): Promise<void> {
        const confirmed =
            window.confirm(
                `Delete the banner for "${productName}"?`,
            );

        if (!confirmed) {
            return;
        }

        try {
            await deleteMutation.mutateAsync(
                bannerId,
            );
        } catch {
            window.alert(
                "Unable to delete home banner.",
            );
        }
    }

    return (
        <div className="space-y-6">
            {/* Header */}

            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-slate-900">
                        Home Banners
                    </h1>

                    <p className="mt-1 text-sm text-slate-500">
                        Manage the promotional banners shown in the mobile home carousel.
                    </p>
                </div>

                {canCreate ? (
                    <button
                        type="button"
                        onClick={() =>
                            navigate(
                                "/home-banners/create",
                            )
                        }
                        className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                    >
                        <Plus
                            size={18}
                        />

                        Add Banner
                    </button>
                ) : null}
            </div>

            {/* Card */}

            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white">
                {/* Filters */}

                <div className="grid gap-3 border-b border-slate-200 p-4 md:grid-cols-[1fr_180px]">
                    <div className="relative">
                        <Search
                            size={18}
                            className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                        />

                        <input
                            type="search"
                            value={
                                search
                            }
                            onChange={(
                                event,
                            ) => {
                                setSearch(
                                    event.target
                                        .value,
                                );

                                setPage(
                                    1,
                                );
                            }}
                            placeholder="Search by product name..."
                            className="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        />
                    </div>

                    <select
                        value={
                            status
                        }
                        onChange={(
                            event,
                        ) => {
                            setStatus(
                                event.target
                                    .value as StatusFilter,
                            );

                            setPage(
                                1,
                            );
                        }}
                        className="rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="all">
                            All Statuses
                        </option>

                        <option value="active">
                            Active
                        </option>

                        <option value="inactive">
                            Inactive
                        </option>
                    </select>
                </div>

                {/* Loading */}

                {bannersQuery.isLoading ? (
                    <div className="p-12 text-center text-sm text-slate-500">
                        Loading banners...
                    </div>
                ) : bannersQuery.isError ? (
                    <div className="p-12 text-center">
                        <p className="text-sm text-red-600">
                            Unable to load home banners.
                        </p>

                        <button
                            type="button"
                            onClick={() => {
                                void bannersQuery.refetch();
                            }}
                            className="mt-3 text-sm font-semibold text-blue-600 transition hover:text-blue-700"
                        >
                            Try Again
                        </button>
                    </div>
                ) : banners.length ===
                  0 ? (
                    /* Empty */

                    <div className="p-12 text-center">
                        <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                            <ImageIcon
                                size={24}
                                className="text-slate-400"
                            />
                        </div>

                        <p className="mt-4 text-sm font-semibold text-slate-700">
                            No home banners found.
                        </p>

                        <p className="mt-1 text-sm text-slate-500">
                            Create a banner and link it to a product.
                        </p>

                        {canCreate ? (
                            <button
                                type="button"
                                onClick={() =>
                                    navigate(
                                        "/home-banners/create",
                                    )
                                }
                                className="mt-4 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                            >
                                <Plus
                                    size={17}
                                />

                                Add Banner
                            </button>
                        ) : null}
                    </div>
                ) : (
                    <>
                        {/* Table */}

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200">
                                <thead className="bg-slate-50">
                                    <tr>
                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Image
                                        </th>

                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Product
                                        </th>

                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Schedule
                                        </th>

                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Order
                                        </th>

                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Status
                                        </th>

                                        {canUpdate ||
                                        canDelete ? (
                                            <th className="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Actions
                                            </th>
                                        ) : null}
                                    </tr>
                                </thead>

                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {banners.map(
                                        (
                                            banner,
                                        ) => (
                                            <tr
                                                key={
                                                    banner.id
                                                }
                                                className="transition hover:bg-slate-50/70"
                                            >
                                                {/* Image */}

                                                <td className="px-5 py-4">
                                                    <div className="h-[72px] w-[132px] overflow-hidden rounded-lg border border-slate-200 bg-slate-100">
                                                        {banner.image_url ? (
                                                            <img
                                                                src={
                                                                    banner.image_url
                                                                }
                                                                alt={
                                                                    banner
                                                                        .product
                                                                        .name
                                                                }
                                                                loading="lazy"
                                                                className="h-full w-full object-cover"
                                                            />
                                                        ) : (
                                                            <div className="flex h-full items-center justify-center">
                                                                <ImageIcon
                                                                    size={
                                                                        22
                                                                    }
                                                                    className="text-slate-400"
                                                                />
                                                            </div>
                                                        )}
                                                    </div>
                                                </td>

                                                {/* Product */}

                                                <td className="px-5 py-4">
                                                    <div className="min-w-[180px]">
                                                        <p className="text-sm font-semibold text-slate-900">
                                                            {
                                                                banner
                                                                    .product
                                                                    .name
                                                            }
                                                        </p>

                                                        <p className="mt-1 text-xs text-slate-500">
                                                            {
                                                                banner
                                                                    .product
                                                                    .slug
                                                            }
                                                        </p>

                                                        {!banner
                                                            .product
                                                            .is_active ? (
                                                            <span className="mt-2 inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">
                                                                Product inactive
                                                            </span>
                                                        ) : null}
                                                    </div>
                                                </td>

                                                {/* Schedule */}

                                                <td className="px-5 py-4">
                                                    <div className="min-w-[190px] text-xs text-slate-600">
                                                        <div className="flex gap-1">
                                                            <span className="font-medium text-slate-500">
                                                                Start:
                                                            </span>

                                                            <span>
                                                                {formatDate(
                                                                    banner.starts_at,
                                                                )}
                                                            </span>
                                                        </div>

                                                        <div className="mt-1 flex gap-1">
                                                            <span className="font-medium text-slate-500">
                                                                End:
                                                            </span>

                                                            <span>
                                                                {formatDate(
                                                                    banner.ends_at,
                                                                )}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>

                                                {/* Order */}

                                                <td className="px-5 py-4">
                                                    <span className="text-sm font-medium text-slate-700">
                                                        {
                                                            banner.sort_order
                                                        }
                                                    </span>
                                                </td>

                                                {/* Status */}

                                                <td className="px-5 py-4">
                                                    <span
                                                        className={[
                                                            "inline-flex rounded-full px-2.5 py-1 text-xs font-semibold",

                                                            banner.is_active
                                                                ? "bg-emerald-50 text-emerald-700"
                                                                : "bg-slate-100 text-slate-600",
                                                        ].join(
                                                            " ",
                                                        )}
                                                    >
                                                        {banner.is_active
                                                            ? "Active"
                                                            : "Inactive"}
                                                    </span>
                                                </td>

                                                {/* Actions */}

                                                {canUpdate ||
                                                canDelete ? (
                                                    <td className="px-5 py-4">
                                                        <div className="flex justify-end gap-2">
                                                            {canUpdate ? (
                                                                <button
                                                                    type="button"
                                                                    title="Edit banner"
                                                                    aria-label={`Edit banner for ${banner.product.name}`}
                                                                    onClick={() =>
                                                                        navigate(
                                                                            `/home-banners/${banner.id}/edit`,
                                                                        )
                                                                    }
                                                                    className="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                                                                >
                                                                    <Edit3
                                                                        size={
                                                                            16
                                                                        }
                                                                    />
                                                                </button>
                                                            ) : null}

                                                            {canDelete ? (
                                                                <button
                                                                    type="button"
                                                                    title="Delete banner"
                                                                    aria-label={`Delete banner for ${banner.product.name}`}
                                                                    disabled={
                                                                        deleteMutation.isPending
                                                                    }
                                                                    onClick={() => {
                                                                        void handleDelete(
                                                                            banner.id,
                                                                            banner
                                                                                .product
                                                                                .name,
                                                                        );
                                                                    }}
                                                                    className="rounded-lg border border-red-200 p-2 text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                                                                >
                                                                    <Trash2
                                                                        size={
                                                                            16
                                                                        }
                                                                    />
                                                                </button>
                                                            ) : null}
                                                        </div>
                                                    </td>
                                                ) : null}
                                            </tr>
                                        ),
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}

                        <div className="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <p className="text-sm text-slate-500">
                                Showing{" "}
                                {pagination?.from ??
                                    0}
                                –
                                {pagination?.to ??
                                    0}{" "}
                                of{" "}
                                {pagination?.total ??
                                    0}
                            </p>

                            <div className="flex gap-2">
                                <button
                                    type="button"
                                    disabled={
                                        !pagination ||
                                        pagination.current_page <=
                                            1
                                    }
                                    onClick={() =>
                                        setPage(
                                            (
                                                current,
                                            ) =>
                                                Math.max(
                                                    current -
                                                        1,
                                                    1,
                                                ),
                                        )
                                    }
                                    className="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    Previous
                                </button>

                                <span className="flex items-center px-2 text-sm text-slate-500">
                                    {pagination?.current_page ??
                                        1}{" "}
                                    /{" "}
                                    {pagination?.last_page ??
                                        1}
                                </span>

                                <button
                                    type="button"
                                    disabled={
                                        !pagination ||
                                        pagination.current_page >=
                                            pagination.last_page
                                    }
                                    onClick={() =>
                                        setPage(
                                            (
                                                current,
                                            ) =>
                                                current +
                                                1,
                                        )
                                    }
                                    className="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
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