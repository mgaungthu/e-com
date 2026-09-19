import { useMemo, useState } from "react";

import axios from "axios";

import {
    CreditCard,
    Edit3,
    ImageIcon,
    Plus,
    Search,
    Trash2,
} from "lucide-react";

import { useNavigate } from "react-router-dom";

import { useDeletePaymentMethod } from "@/features/payment-methods/hooks/usePaymentMethodMutations";
import { usePaymentMethods } from "@/features/payment-methods/hooks/usePaymentMethods";

import type { PaymentMethodType } from "@/features/payment-methods/types/paymentMethod.types";

import { useAuthStore } from "@/store/authStore";

type PaymentMethodStatus = "all" | "active" | "inactive";

type PaymentMethodTypeFilter = "all" | PaymentMethodType;

type ErrorResponse = {
    message?: string;
};

function formatPaymentMethodType(type: PaymentMethodType) {
    switch (type) {
        case "cod":
            return "Cash on Delivery";

        case "ewallet":
            return "E-Wallet";

        default:
            return type;
    }
}

function getPaymentMethodTypeClassName(type: PaymentMethodType) {
    switch (type) {
        case "cod":
            return "bg-amber-50 text-amber-700";

        case "ewallet":
            return "bg-blue-50 text-blue-700";

        default:
            return "bg-slate-100 text-slate-600";
    }
}

export default function PaymentMethodsPage() {
    const [page, setPage] = useState(1);

    const [search, setSearch] = useState("");

    const [status, setStatus] = useState<PaymentMethodStatus>("all");

    const [type, setType] = useState<PaymentMethodTypeFilter>("all");

    const navigate = useNavigate();

    const permissions = useAuthStore((state) => state.user?.permissions ?? []);

    const canCreate = permissions.includes("payment_methods.create");

    const canUpdate = permissions.includes("payment_methods.update");

    const canDelete = permissions.includes("payment_methods.delete");

    const filters = useMemo(
        () => ({
            page,

            search: search || undefined,

            status,

            type,

            per_page: 15,
        }),
        [page, search, status, type],
    );

    const paymentMethodsQuery = usePaymentMethods(filters);

    const deleteMutation = useDeletePaymentMethod();

    const pagination = paymentMethodsQuery.data?.data;

    const paymentMethods = pagination?.data ?? [];

    async function handleDelete(
        paymentMethodId: number,
        paymentMethodName: string,
    ) {
        const confirmed = window.confirm(
            `Delete "${paymentMethodName}" payment method?`,
        );

        if (!confirmed) {
            return;
        }

        try {
            await deleteMutation.mutateAsync(paymentMethodId);
        } catch (error) {
            if (axios.isAxiosError<ErrorResponse>(error)) {
                window.alert(
                    error.response?.data.message ??
                        "Unable to delete payment method.",
                );

                return;
            }

            window.alert("Unable to delete payment method.");
        }
    }

    return (
        <div className="space-y-6">
            {/*
            |--------------------------------------------------------------------------
            | Page Header
            |--------------------------------------------------------------------------
            */}

            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-slate-900">
                        Payment Methods
                    </h1>

                    <p className="mt-1 text-sm text-slate-500">
                        Manage checkout payment options, wallet accounts, QR
                        codes, and payment proof requirements.
                    </p>
                </div>

                {canCreate ? (
                    <button
                        type="button"
                        onClick={() => navigate("/payment-methods/create")}
                        className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                    >
                        <Plus size={18} />
                        Add payment method
                    </button>
                ) : null}
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
                            placeholder="Search name, code, or account..."
                            className="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        />
                    </div>

                    <select
                        value={type}
                        onChange={(event) => {
                            setType(
                                event.target.value as PaymentMethodTypeFilter,
                            );

                            setPage(1);
                        }}
                        className="rounded-lg border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="all">All types</option>

                        <option value="ewallet">E-Wallet</option>

                        <option value="cod">Cash on Delivery</option>
                    </select>

                    <select
                        value={status}
                        onChange={(event) => {
                            setStatus(
                                event.target.value as PaymentMethodStatus,
                            );

                            setPage(1);
                        }}
                        className="rounded-lg border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="all">All statuses</option>

                        <option value="active">Active</option>

                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                {/*
                |--------------------------------------------------------------------------
                | Loading / Error / Empty
                |--------------------------------------------------------------------------
                */}

                {paymentMethodsQuery.isLoading ? (
                    <div className="p-10 text-center text-sm text-slate-500">
                        Loading payment methods...
                    </div>
                ) : paymentMethodsQuery.isError ? (
                    <div className="p-10 text-center">
                        <p className="text-sm text-red-600">
                            Unable to load payment methods.
                        </p>

                        <button
                            type="button"
                            onClick={() => {
                                void paymentMethodsQuery.refetch();
                            }}
                            className="mt-3 text-sm font-medium text-blue-600 transition hover:text-blue-700"
                        >
                            Try again
                        </button>
                    </div>
                ) : paymentMethods.length === 0 ? (
                    <div className="p-10 text-center">
                        <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <CreditCard size={22} />
                        </div>

                        <p className="mt-4 text-sm font-medium text-slate-700">
                            No payment methods found.
                        </p>

                        <p className="mt-1 text-sm text-slate-500">
                            Create a payment method to make it available during
                            checkout.
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
                                            Payment Method
                                        </th>

                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Type
                                        </th>

                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Account
                                        </th>

                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Proof
                                        </th>

                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Order
                                        </th>

                                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Status
                                        </th>

                                        {canUpdate || canDelete ? (
                                            <th className="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Actions
                                            </th>
                                        ) : null}
                                    </tr>
                                </thead>

                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {paymentMethods.map((paymentMethod) => (
                                        <tr
                                            key={paymentMethod.id}
                                            className="transition hover:bg-slate-50"
                                        >
                                            {/*
                                                |--------------------------------------------------------------------------
                                                | Payment Method
                                                |--------------------------------------------------------------------------
                                                */}

                                            <td className="px-5 py-4">
                                                <div className="flex items-center gap-3">
                                                    {paymentMethod.logo_url ? (
                                                        <img
                                                            src={
                                                                paymentMethod.logo_url
                                                            }
                                                            alt={
                                                                paymentMethod.name
                                                            }
                                                            className="h-11 w-11 shrink-0 rounded-lg border border-slate-200 bg-white object-contain p-1"
                                                        />
                                                    ) : (
                                                        <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                                                            <ImageIcon
                                                                size={19}
                                                            />
                                                        </div>
                                                    )}

                                                    <div className="min-w-0">
                                                        <p className="max-w-xs truncate font-medium text-slate-900">
                                                            {paymentMethod.name}
                                                        </p>

                                                        <p className="mt-0.5 text-xs text-slate-500">
                                                            {paymentMethod.code}
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>

                                            {/*
                                                |--------------------------------------------------------------------------
                                                | Type
                                                |--------------------------------------------------------------------------
                                                */}

                                            <td className="px-5 py-4">
                                                <span
                                                    className={[
                                                        "inline-flex rounded-full px-2.5 py-1 text-xs font-semibold",
                                                        getPaymentMethodTypeClassName(
                                                            paymentMethod.type,
                                                        ),
                                                    ].join(" ")}
                                                >
                                                    {formatPaymentMethodType(
                                                        paymentMethod.type,
                                                    )}
                                                </span>
                                            </td>

                                            {/*
                                                |--------------------------------------------------------------------------
                                                | Account
                                                |--------------------------------------------------------------------------
                                                */}

                                            <td className="px-5 py-4">
                                                {paymentMethod.account_name ||
                                                paymentMethod.account_number ? (
                                                    <div>
                                                        <p className="text-sm font-medium text-slate-800">
                                                            {paymentMethod.account_name ??
                                                                "—"}
                                                        </p>

                                                        <p className="mt-0.5 text-xs text-slate-500">
                                                            {paymentMethod.account_number ??
                                                                "—"}
                                                        </p>
                                                    </div>
                                                ) : (
                                                    <span className="text-sm text-slate-400">
                                                        —
                                                    </span>
                                                )}
                                            </td>

                                            {/*
                                                |--------------------------------------------------------------------------
                                                | Proof Requirement
                                                |--------------------------------------------------------------------------
                                                */}

                                            <td className="px-5 py-4">
                                                <span
                                                    className={[
                                                        "inline-flex rounded-full px-2.5 py-1 text-xs font-semibold",
                                                        paymentMethod.requires_proof
                                                            ? "bg-violet-50 text-violet-700"
                                                            : "bg-slate-100 text-slate-600",
                                                    ].join(" ")}
                                                >
                                                    {paymentMethod.requires_proof
                                                        ? "Required"
                                                        : "Not required"}
                                                </span>
                                            </td>

                                            {/*
                                                |--------------------------------------------------------------------------
                                                | Sort Order
                                                |--------------------------------------------------------------------------
                                                */}

                                            <td className="px-5 py-4 text-sm font-medium text-slate-700">
                                                {paymentMethod.sort_order}
                                            </td>

                                            {/*
                                                |--------------------------------------------------------------------------
                                                | Status
                                                |--------------------------------------------------------------------------
                                                */}

                                            <td className="px-5 py-4">
                                                <span
                                                    className={[
                                                        "inline-flex rounded-full px-2.5 py-1 text-xs font-semibold",
                                                        paymentMethod.is_active
                                                            ? "bg-emerald-50 text-emerald-700"
                                                            : "bg-slate-100 text-slate-600",
                                                    ].join(" ")}
                                                >
                                                    {paymentMethod.is_active
                                                        ? "Active"
                                                        : "Inactive"}
                                                </span>
                                            </td>

                                            {/*
                                                |--------------------------------------------------------------------------
                                                | Actions
                                                |--------------------------------------------------------------------------
                                                */}

                                            {canUpdate || canDelete ? (
                                                <td className="px-5 py-4">
                                                    <div className="flex justify-end gap-2">
                                                        {canUpdate ? (
                                                            <button
                                                                type="button"
                                                                onClick={() =>
                                                                    navigate(
                                                                        `/payment-methods/${paymentMethod.id}/edit`,
                                                                    )
                                                                }
                                                                className="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:bg-slate-100"
                                                                aria-label={`Edit ${paymentMethod.name}`}
                                                            >
                                                                <Edit3
                                                                    size={16}
                                                                />
                                                            </button>
                                                        ) : null}

                                                        {canDelete ? (
                                                            <button
                                                                type="button"
                                                                disabled={
                                                                    deleteMutation.isPending
                                                                }
                                                                onClick={() => {
                                                                    void handleDelete(
                                                                        paymentMethod.id,
                                                                        paymentMethod.name,
                                                                    );
                                                                }}
                                                                className="rounded-lg border border-red-200 p-2 text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                                                                aria-label={`Delete ${paymentMethod.name}`}
                                                            >
                                                                <Trash2
                                                                    size={16}
                                                                />
                                                            </button>
                                                        ) : null}
                                                    </div>
                                                </td>
                                            ) : null}
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/*
                        |--------------------------------------------------------------------------
                        | Pagination
                        |--------------------------------------------------------------------------
                        */}

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
                                        pagination.current_page <= 1
                                    }
                                    onClick={() => {
                                        setPage((current) =>
                                            Math.max(current - 1, 1),
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
                                        setPage((current) => current + 1);
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
