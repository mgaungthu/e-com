import {
    useMemo,
    useState,
} from "react";

import {
    Edit3,
    MapPin,
    Plus,
    Search,
} from "lucide-react";

import { useNavigate } from "react-router-dom";

import {
    getLocationTypeLabel,
    LOCATION_TYPE_OPTIONS,
} from "@/features/locations/constants/location-form.constants";

import { useLocations } from "@/features/locations/hooks/useLocations";

import { formatMmk } from "@/utils/currency";

export default function LocationsPage() {
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
    ] = useState<
        | "all"
        | "active"
        | "inactive"
    >("all");

    const [
        type,
        setType,
    ] = useState("all");

    const navigate =
        useNavigate();

    const filters =
        useMemo(
            () => ({
                page,

                search,

                status,

                type:
                    type === "all"
                        ? undefined
                        : type,

                perPage: 15,
            }),
            [
                page,
                search,
                status,
                type,
            ],
        );

    const locationsQuery =
        useLocations(
            filters,
        );

    const locations =
        locationsQuery.data
            ?.data.data ?? [];

    const pagination =
        locationsQuery.data
            ?.data;

    return (
        <div className="space-y-6">
            {/*
            |--------------------------------------------------------------------------
            | Header
            |--------------------------------------------------------------------------
            */}

            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-slate-900">
                        Locations
                    </h1>

                    <p className="mt-1 text-sm text-slate-500">
                        Manage delivery areas,
                        hierarchy, and shipping
                        fees.
                    </p>
                </div>

                <button
                    type="button"
                    onClick={() =>
                        navigate(
                            "/locations/create",
                        )
                    }
                    className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                >
                    <Plus size={18} />

                    Add location
                </button>
            </div>

            {/*
            |--------------------------------------------------------------------------
            | List Card
            |--------------------------------------------------------------------------
            */}

            <div className="rounded-xl border border-slate-200 bg-white">
                {/*
                |--------------------------------------------------------------------------
                | Filters
                |--------------------------------------------------------------------------
                */}

                <div className="flex flex-col gap-3 border-b border-slate-200 p-4 md:flex-row">
                    <div className="relative flex-1">
                        <Search
                            size={18}
                            className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                        />

                        <input
                            type="search"
                            value={search}
                            onChange={(
                                event,
                            ) => {
                                setSearch(
                                    event
                                        .target
                                        .value,
                                );

                                setPage(1);
                            }}
                            placeholder="Search locations..."
                            className="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        />
                    </div>

                    <select
                        value={type}
                        onChange={(
                            event,
                        ) => {
                            setType(
                                event
                                    .target
                                    .value,
                            );

                            setPage(1);
                        }}
                        className="rounded-lg border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-blue-500"
                    >
                        <option value="all">
                            All types
                        </option>

                        {LOCATION_TYPE_OPTIONS.map(
                            (
                                option,
                            ) => (
                                <option
                                    key={
                                        option.value
                                    }
                                    value={
                                        option.value
                                    }
                                >
                                    {
                                        option.label
                                    }
                                </option>
                            ),
                        )}
                    </select>

                    <select
                        value={status}
                        onChange={(
                            event,
                        ) => {
                            setStatus(
                                event
                                    .target
                                    .value as
                                    | "all"
                                    | "active"
                                    | "inactive",
                            );

                            setPage(1);
                        }}
                        className="rounded-lg border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-blue-500"
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
                </div>

                {/*
                |--------------------------------------------------------------------------
                | Loading
                |--------------------------------------------------------------------------
                */}

                {locationsQuery.isLoading ? (
                    <div className="p-10 text-center text-sm text-slate-500">
                        Loading locations...
                    </div>
                ) : locationsQuery.isError ? (
                    <div className="p-10 text-center">
                        <p className="text-sm text-red-600">
                            Unable to load
                            locations.
                        </p>

                        <button
                            type="button"
                            onClick={() => {
                                void locationsQuery.refetch();
                            }}
                            className="mt-3 text-sm font-medium text-blue-600"
                        >
                            Try again
                        </button>
                    </div>
                ) : locations.length ===
                  0 ? (
                    <div className="p-10 text-center">
                        <MapPin
                            size={28}
                            className="mx-auto text-slate-300"
                        />

                        <p className="mt-3 text-sm font-medium text-slate-700">
                            No locations found.
                        </p>

                        <p className="mt-1 text-sm text-slate-500">
                            Create your first
                            delivery location to
                            get started.
                        </p>
                    </div>
                ) : (
                    <>
                        {/*
                        |--------------------------------------------------------------------------
                        | Table
                        |--------------------------------------------------------------------------
                        */}

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200">
                                <thead className="bg-slate-50">
                                    <tr>
                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Location
                                        </th>

                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Type
                                        </th>

                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Parent
                                        </th>

                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Shipping Fee
                                        </th>

                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Children
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
                                    {locations.map(
                                        (
                                            location,
                                        ) => (
                                            <tr
                                                key={
                                                    location.id
                                                }
                                                className="hover:bg-slate-50"
                                            >
                                                <td className="px-5 py-4">
                                                    <div className="flex items-center gap-3">
                                                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                                            <MapPin
                                                                size={
                                                                    18
                                                                }
                                                            />
                                                        </div>

                                                        <div>
                                                            <p className="font-medium text-slate-900">
                                                                {
                                                                    location.name_en
                                                                }
                                                            </p>

                                                            {location.name_mm ? (
                                                                <p className="mt-0.5 text-xs text-slate-500">
                                                                    {
                                                                        location.name_mm
                                                                    }
                                                                </p>
                                                            ) : null}
                                                        </div>
                                                    </div>
                                                </td>

                                                <td className="px-5 py-4">
                                                    <span className="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                                        {getLocationTypeLabel(
                                                            location.type,
                                                        )}
                                                    </span>
                                                </td>

                                                <td className="px-5 py-4 text-sm text-slate-600">
                                                    {location
                                                        .parent
                                                        ?.name_en ??
                                                        "—"}
                                                </td>

                                                <td className="px-5 py-4">
                                                    {location.shipping_fee !==
                                                    null ? (
                                                        <span className="text-sm font-medium text-slate-900">
                                                            {formatMmk(
                                                                location.shipping_fee,
                                                            )}
                                                        </span>
                                                    ) : (
                                                        <div>
                                                            <span className="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                                                Inherit
                                                            </span>

                                                            <p className="mt-1 text-[11px] text-slate-400">
                                                                Parent
                                                                or
                                                                global
                                                                fee
                                                            </p>
                                                        </div>
                                                    )}
                                                </td>

                                                <td className="px-5 py-4 text-sm text-slate-600">
                                                    {
                                                        location.children_count
                                                    }
                                                </td>

                                                <td className="px-5 py-4">
                                                    <span
                                                        className={[
                                                            "inline-flex rounded-full px-2.5 py-1 text-xs font-semibold",

                                                            location.is_active
                                                                ? "bg-emerald-50 text-emerald-700"
                                                                : "bg-slate-100 text-slate-600",
                                                        ].join(
                                                            " ",
                                                        )}
                                                    >
                                                        {location.is_active
                                                            ? "Active"
                                                            : "Inactive"}
                                                    </span>
                                                </td>

                                                <td className="px-5 py-4 text-sm text-slate-600">
                                                    {
                                                        location.sort_order
                                                    }
                                                </td>

                                                <td className="px-5 py-4">
                                                    <div className="flex justify-end gap-2">
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                navigate(
                                                                    `/locations/${location.id}/edit`,
                                                                )
                                                            }
                                                            className="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:bg-slate-100"
                                                            aria-label={`Edit ${location.name_en}`}
                                                        >
                                                            <Edit3
                                                                size={
                                                                    16
                                                                }
                                                            />
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ),
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/*
                        |--------------------------------------------------------------------------
                        | Pagination
                        |--------------------------------------------------------------------------
                        */}

                        <div className="flex items-center justify-between border-t border-slate-200 px-5 py-4">
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
                                    onClick={() => {
                                        setPage(
                                            (
                                                current,
                                            ) =>
                                                Math.max(
                                                    current -
                                                        1,
                                                    1,
                                                ),
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
                                        setPage(
                                            (
                                                current,
                                            ) =>
                                                current +
                                                1,
                                        );
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