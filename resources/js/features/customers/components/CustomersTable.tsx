import { Eye, UserRound } from "lucide-react";
import { Link } from "react-router-dom";

import type { Customer } from "../types/customer.types";
import { CustomerStatusBadge } from "./CustomerStatusBadge";

type CustomersTableProps = {
    customers: Customer[];
};

function getCustomerName(
    customer: Customer,
): string {
    return (
        customer.display_name ||
        [customer.first_name, customer.last_name]
            .filter(Boolean)
            .join(" ") ||
        customer.name ||
        "Unnamed customer"
    );
}

export function CustomersTable({
    customers,
}: CustomersTableProps) {
    return (
        <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-slate-200">
                <thead className="bg-slate-50">
                    <tr>
                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Customer
                        </th>

                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Contact
                        </th>

                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Orders
                        </th>

                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Status
                        </th>

                        <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Joined
                        </th>

                        <th className="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody className="divide-y divide-slate-100 bg-white">
                    {customers.map((customer) => {
                        const profile =
                            customer.customer_profile ??
                            customer.customerProfile;

                        return (
                            <tr
                                key={customer.id}
                                className="transition hover:bg-slate-50"
                            >
                                <td className="whitespace-nowrap px-5 py-4">
                                    <div className="flex items-center gap-3">
                                        <div className="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-100 text-slate-500">
                                            {customer.avatar_url ? (
                                                <img
                                                    src={
                                                        customer.avatar_url
                                                    }
                                                    alt={getCustomerName(
                                                        customer,
                                                    )}
                                                    className="h-full w-full object-cover"
                                                />
                                            ) : (
                                                <UserRound
                                                    size={18}
                                                />
                                            )}
                                        </div>

                                        <div>
                                            <p className="text-sm font-semibold text-slate-900">
                                                {getCustomerName(
                                                    customer,
                                                )}
                                            </p>

                                            <p className="mt-0.5 text-xs text-slate-500">
                                                ID #{customer.id}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td className="px-5 py-4">
                                    <p className="text-sm text-slate-700">
                                        {customer.email}
                                    </p>

                                    <p className="mt-0.5 text-xs text-slate-500">
                                        {customer.phone ??
                                            "No phone"}
                                    </p>
                                </td>

                                <td className="px-5 py-4">
                                    <p className="text-sm font-semibold text-slate-900">
                                        {profile?.total_orders ??
                                            0}
                                    </p>

                                    <p className="mt-0.5 text-xs text-slate-500">
                                        Total orders
                                    </p>
                                </td>

                                <td className="px-5 py-4">
                                    <CustomerStatusBadge
                                        status={
                                            customer.status
                                        }
                                    />
                                </td>

                                <td className="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {customer.created_at
                                        ? new Date(
                                              customer.created_at,
                                          ).toLocaleDateString()
                                        : "—"}
                                </td>

                                <td className="px-5 py-4 text-right">
                                    <Link
                                        to={`/customers/${customer.id}`}
                                        className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                                    >
                                        <Eye size={15} />
                                        View
                                    </Link>
                                </td>
                            </tr>
                        );
                    })}
                </tbody>
            </table>
        </div>
    );
}