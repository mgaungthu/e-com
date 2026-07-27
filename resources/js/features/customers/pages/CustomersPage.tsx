import {
    Search,
    UsersRound,
} from "lucide-react";
import {
    useEffect,
    useState,
} from "react";

import {
    CUSTOMER_ORDER_OPTIONS,
    CUSTOMER_PAGE_SIZE,
    CUSTOMER_STATUS_OPTIONS,
} from "../constants/customer.constants";
import { CustomersTable } from "../components/CustomersTable";
import { useCustomers } from "../hooks/useCustomers";
import type {
    CustomerOrderFilter,
    CustomerStatus,
} from "../types/customer.types";

export default function CustomersPage() {
    const [page, setPage] = useState(1);
    const [search, setSearch] =
        useState("");
    const [debouncedSearch, setDebouncedSearch] =
        useState("");
    const [status, setStatus] =
        useState<CustomerStatus | "all">(
            "all",
        );
    const [orderStatus, setOrderStatus] =
        useState<CustomerOrderFilter>(
            "all",
        );

    useEffect(() => {
        const timeout =
            window.setTimeout(() => {
                setDebouncedSearch(
                    search.trim(),
                );
                setPage(1);
            }, 350);

        return () => {
            window.clearTimeout(timeout);
        };
    }, [search]);

    const customersQuery = useCustomers({
        page,
        perPage: CUSTOMER_PAGE_SIZE,
        search: debouncedSearch,
        status,
        orderStatus,
    });

    const pagination =
        customersQuery.data?.data;

    const customers =
        pagination?.data ?? [];

    function handleStatusChange(
        nextStatus:
            | CustomerStatus
            | "all",
    ) {
        setStatus(nextStatus);
        setPage(1);
    }

    function handleOrderStatusChange(
        nextStatus: CustomerOrderFilter,
    ) {
        setOrderStatus(nextStatus);
        setPage(1);
    }

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-2xl font-bold text-slate-900">
                    Customers
                </h1>

                <p className="mt-1 text-sm text-slate-500">
                    View and manage customer accounts,
                    profiles, addresses, and activity.
                </p>
            </div>

            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <div className="border-b border-slate-200 p-5">
                    <div className="grid gap-3 lg:grid-cols-[minmax(260px,1fr)_200px_200px]">
                        <div className="relative">
                            <Search
                                size={18}
                                className="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"
                            />

                            <input
                                type="search"
                                value={search}
                                onChange={(event) =>
                                    setSearch(
                                        event.target
                                            .value,
                                    )
                                }
                                placeholder="Search by name, email, or phone..."
                                className="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            />
                        </div>

                        <select
                            value={status}
                            onChange={(event) =>
                                handleStatusChange(
                                    event.target
                                        .value as
                                        | CustomerStatus
                                        | "all",
                                )
                            }
                            className="rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        >
                            {CUSTOMER_STATUS_OPTIONS.map(
                                (option) => (
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
                            value={orderStatus}
                            onChange={(event) =>
                                handleOrderStatusChange(
                                    event.target
                                        .value as CustomerOrderFilter,
                                )
                            }
                            className="rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        >
                            {CUSTOMER_ORDER_OPTIONS.map(
                                (option) => (
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
                    </div>
                </div>

                {customersQuery.isLoading ? (
                    <div className="p-10 text-center text-sm text-slate-500">
                        Loading customers...
                    </div>
                ) : customersQuery.isError ? (
                    <div className="p-10 text-center">
                        <p className="text-sm font-medium text-red-600">
                            Unable to load customers.
                        </p>

                        <button
                            type="button"
                            onClick={() =>
                                customersQuery.refetch()
                            }
                            className="mt-3 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        >
                            Try again
                        </button>
                    </div>
                ) : customers.length === 0 ? (
                    <div className="flex flex-col items-center p-12 text-center">
                        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                            <UsersRound
                                size={22}
                            />
                        </div>

                        <h2 className="mt-4 font-semibold text-slate-900">
                            No customers found
                        </h2>

                        <p className="mt-1 text-sm text-slate-500">
                            Try changing your search
                            or filters.
                        </p>
                    </div>
                ) : (
                    <CustomersTable
                        customers={customers}
                    />
                )}

                {pagination &&
                    pagination.last_page >
                        1 && (
                        <div className="flex items-center justify-between border-t border-slate-200 px-5 py-4">
                            <p className="text-sm text-slate-500">
                                Showing{" "}
                                {pagination.from ??
                                    0}{" "}
                                to{" "}
                                {pagination.to ?? 0}{" "}
                                of{" "}
                                {pagination.total}{" "}
                                customers
                            </p>

                            <div className="flex gap-2">
                                <button
                                    type="button"
                                    disabled={
                                        page <= 1
                                    }
                                    onClick={() =>
                                        setPage(
                                            (
                                                current,
                                            ) =>
                                                current -
                                                1,
                                        )
                                    }
                                    className="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Previous
                                </button>

                                <button
                                    type="button"
                                    disabled={
                                        page >=
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
                                    className="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                    )}
            </div>
        </div>
    );
}