import { useMemo, useState } from "react";

import axios from "axios";
import {
    AlertCircle,
    CreditCard,
    ImageIcon,
    MapPin,
    Package,
    ReceiptText,
    UserRound,
} from "lucide-react";
import {
    Navigate,
    useNavigate,
    useParams,
} from "react-router-dom";

import PageHeader from "@/components/PageHeader";
import { OrderStatusBadge } from "@/features/orders/components/OrderStatusBadge";
import { useOrder } from "@/features/orders/hooks/useOrder";
import {
    useUpdateOrderPayment,
    useUpdateOrderStatus,
} from "@/features/orders/hooks/useOrderMutations";
import type {
    OrderStatus,
    PaymentStatus,
} from "@/features/orders/types/order.types";
import { useAuthStore } from "@/store/authStore";
import { formatMmk } from "@/utils/currency";

function formatDate(
    value: string | null | undefined,
): string {
    if (!value) {
        return "Not available";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return "Not available";
    }

    return new Intl.DateTimeFormat("en-US", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(date);
}

function formatStatus(value: string): string {
    return value
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) =>
            character.toUpperCase(),
        );
}

function formatPaymentMethod(
    value: string | null | undefined,
): string {
    if (!value) {
        return "Not available";
    }

    const labels: Record<string, string> = {
        kbzpay: "KBZPay",
        wavepay: "WavePay",
        ayapay: "AYA Pay",
        cbpay: "CB Pay",
        mpitesan: "M-Pitesan",
        cod: "Cash on Delivery",
        cash: "Cash",
        bank_transfer: "Bank Transfer",
    };

    return labels[value.toLowerCase()] ?? value;
}

function DetailCard({
    icon: Icon,
    title,
    children,
}: {
    icon?: React.ComponentType<{
        size?: number;
        className?: string;
    }>;
    title: string;
    children: React.ReactNode;
}) {
    return (
        <section className="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div className="flex items-center gap-2 border-b border-slate-200 px-5 py-4">
                {Icon ? (
                    <Icon
                        size={18}
                        className="text-slate-400"
                    />
                ) : null}

                <h2 className="font-semibold text-slate-900">
                    {title}
                </h2>
            </div>

            {children}
        </section>
    );
}

type AddressLike = {
    id?: number | null;

    name?: string | null;
    full_name?: string | null;
    recipient_name?: string | null;

    phone?: string | null;
    alternate_phone?: string | null;

    type?: string | null;
    label?: string | null;

    unit?: string | null;
    floor?: string | null;
    building?: string | null;

    street?: string | null;
    address?: string | null;

    address_line_one?: string | null;
    address_line_two?: string | null;

    address_line_one?: string | null;
    address_line_two?: string | null;

    township?: string | null;
    district?: string | null;
    city?: string | null;
    state?: string | null;
    region?: string | null;

    postal_code?: string | null;
    country?: string | null;
    country_code?: string | null;

    landmark?: string | null;
    delivery_instruction?: string | null;
};

type AddressCardProps = {
    title: string;
    address?: AddressLike | null;
};

function AddressCard({
    title,
    address,
}: AddressCardProps) {
    const recipientName =
        address?.full_name ||
        address?.recipient_name ||
        address?.name ||
        null;

    const primaryAddress =
        address?.address_line_one ||
        address?.address_line_one ||
        address?.address ||
        address?.street ||
        null;

    const secondaryAddress =
        address?.address_line_two ||
        address?.address_line_two ||
        null;

    const buildingLine = [
        address?.unit
            ? `Unit ${address.unit}`
            : null,
        address?.floor
            ? `Floor ${address.floor}`
            : null,
        address?.building,
    ]
        .filter(Boolean)
        .join(", ");

    const locality = [
        address?.township,
        address?.district,
        address?.city,
        address?.state || address?.region,
        address?.postal_code,
    ]
        .filter(Boolean)
        .join(", ");

    const hasDisplayableAddress =
        Boolean(recipientName) ||
        Boolean(address?.phone) ||
        Boolean(buildingLine) ||
        Boolean(primaryAddress) ||
        Boolean(secondaryAddress) ||
        Boolean(locality) ||
        Boolean(address?.country) ||
        Boolean(address?.country_code);

    return (
        <section className="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div className="flex items-center gap-2 border-b border-slate-200 px-5 py-4">
                <MapPin
                    size={18}
                    className="text-slate-400"
                />

                <h2 className="font-semibold text-slate-900">
                    {title}
                </h2>
            </div>

            {address ? (
                <div className="p-5 text-sm leading-6 text-slate-600">
                    {recipientName ? (
                        <p className="font-semibold text-slate-900">
                            {recipientName}
                        </p>
                    ) : null}

                    {address.label || address.type ? (
                        <p className="text-slate-500">
                            {address.label ||
                                formatStatus(
                                    address.type ?? "",
                                )}
                        </p>
                    ) : null}

                    {address.phone ? (
                        <p>{address.phone}</p>
                    ) : null}

                    {address.alternate_phone ? (
                        <p>
                            Alternate:{" "}
                            {address.alternate_phone}
                        </p>
                    ) : null}

                    {buildingLine ? (
                        <p>{buildingLine}</p>
                    ) : null}

                    {primaryAddress ? (
                        <p>{primaryAddress}</p>
                    ) : null}

                    {secondaryAddress ? (
                        <p>{secondaryAddress}</p>
                    ) : null}

                    {locality ? (
                        <p>{locality}</p>
                    ) : null}

                    {address.country ||
                    address.country_code ? (
                        <p>
                            {address.country ||
                                address.country_code}
                        </p>
                    ) : null}

                    {address.landmark ? (
                        <p className="mt-2 text-slate-500">
                            Landmark: {address.landmark}
                        </p>
                    ) : null}

                    {address.delivery_instruction ? (
                        <div className="mt-3 rounded-lg bg-slate-50 px-3 py-2 text-slate-600">
                            <span className="font-medium text-slate-700">
                                Delivery instruction:
                            </span>{" "}
                            {address.delivery_instruction}
                        </div>
                    ) : null}

                    {!hasDisplayableAddress ? (
                        <p className="text-slate-500">
                            Address information is available,
                            but no displayable fields were found.
                        </p>
                    ) : null}
                </div>
            ) : (
                <p className="p-5 text-sm text-slate-500">
                    No address available.
                </p>
            )}
        </section>
    );
}

type ActionPanelProps = {
    orderStatus: OrderStatus;
    paymentStatus: PaymentStatus;

    allowedStatuses: OrderStatus[];
    allowedPayments: PaymentStatus[];

    nextStatus: OrderStatus | "";
    nextPayment: PaymentStatus | "";

    note: string;

    statusPending: boolean;
    paymentPending: boolean;

    onStatusChange: (
        value: OrderStatus | "",
    ) => void;

    onPaymentChange: (
        value: PaymentStatus | "",
    ) => void;

    onNoteChange: (value: string) => void;

    onUpdateStatus: () => Promise<void>;
    onUpdatePayment: () => Promise<void>;
};

function OrderActionPanels({
    orderStatus,
    paymentStatus,
    allowedStatuses,
    allowedPayments,
    nextStatus,
    nextPayment,
    note,
    statusPending,
    paymentPending,
    onStatusChange,
    onPaymentChange,
    onNoteChange,
    onUpdateStatus,
    onUpdatePayment,
}: ActionPanelProps) {
    if (
        allowedStatuses.length === 0 &&
        allowedPayments.length === 0
    ) {
        return null;
    }

    return (
        <div className="space-y-4">
            {allowedStatuses.length > 0 ? (
                <section className="rounded-xl border border-slate-200 bg-white p-5">
                    <h2 className="font-semibold text-slate-900">
                        Update fulfillment
                    </h2>

                    <p className="mt-1 text-sm text-slate-500">
                        Current status:{" "}
                        {formatStatus(orderStatus)}
                    </p>

                    <label
                        htmlFor="order-status"
                        className="mt-4 block text-sm font-medium text-slate-700"
                    >
                        Next order status
                    </label>

                    <select
                        id="order-status"
                        value={nextStatus}
                        onChange={(event) =>
                            onStatusChange(
                                event.target.value as
                                    | OrderStatus
                                    | "",
                            )
                        }
                        className="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="">
                            Select next status
                        </option>

                        {allowedStatuses.map(
                            (status) => (
                                <option
                                    key={status}
                                    value={status}
                                >
                                    {formatStatus(status)}
                                </option>
                            ),
                        )}
                    </select>

                    <label
                        htmlFor="status-note"
                        className="mt-3 block text-sm font-medium text-slate-700"
                    >
                        Status note{" "}
                        <span className="font-normal text-slate-400">
                            (optional)
                        </span>
                    </label>

                    <textarea
                        id="status-note"
                        value={note}
                        onChange={(event) =>
                            onNoteChange(
                                event.target.value,
                            )
                        }
                        rows={3}
                        placeholder="Add context for this status change"
                        className="mt-1.5 w-full resize-none rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    />

                    <button
                        type="button"
                        disabled={
                            !nextStatus ||
                            statusPending
                        }
                        onClick={() =>
                            void onUpdateStatus()
                        }
                        className="mt-3 w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {statusPending
                            ? "Updating..."
                            : "Update status"}
                    </button>
                </section>
            ) : null}

            {allowedPayments.length > 0 ? (
                <section className="rounded-xl border border-slate-200 bg-white p-5">
                    <h2 className="font-semibold text-slate-900">
                        Update payment
                    </h2>

                    <p className="mt-1 text-sm text-slate-500">
                        Current status:{" "}
                        {formatStatus(paymentStatus)}
                    </p>

                    <label
                        htmlFor="payment-status"
                        className="mt-4 block text-sm font-medium text-slate-700"
                    >
                        New payment status
                    </label>

                    <select
                        id="payment-status"
                        value={nextPayment}
                        onChange={(event) =>
                            onPaymentChange(
                                event.target.value as
                                    | PaymentStatus
                                    | "",
                            )
                        }
                        className="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="">
                            Select payment status
                        </option>

                        {allowedPayments.map(
                            (status) => (
                                <option
                                    key={status}
                                    value={status}
                                >
                                    {formatStatus(status)}
                                </option>
                            ),
                        )}
                    </select>

                    <button
                        type="button"
                        disabled={
                            !nextPayment ||
                            paymentPending
                        }
                        onClick={() =>
                            void onUpdatePayment()
                        }
                        className="mt-3 w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {paymentPending
                            ? "Updating..."
                            : "Update payment"}
                    </button>
                </section>
            ) : null}
        </div>
    );
}

export default function OrderDetailPage() {
    const navigate = useNavigate();
    const params = useParams();

    const orderId = Number(params.orderId);

    const orderQuery = useOrder(
        Number.isFinite(orderId)
            ? orderId
            : null,
    );

    const statusMutation =
        useUpdateOrderStatus(orderId);

    const paymentMutation =
        useUpdateOrderPayment(orderId);

    const permissions = useAuthStore(
        (state) =>
            state.user?.permissions ?? [],
    );

    const [nextStatus, setNextStatus] =
        useState<OrderStatus | "">("");

    const [nextPayment, setNextPayment] =
        useState<PaymentStatus | "">("");

    const [note, setNote] =
        useState("");

    const [error, setError] =
        useState<string | null>(null);

    const order =
        orderQuery.data?.data.order;

    const allowedStatuses = useMemo(
        () =>
            (
                order?.available_statuses ?? []
            ).filter((status) =>
                status === "cancelled"
                    ? permissions.includes(
                          "orders.cancel",
                      )
                    : permissions.includes(
                          "orders.update",
                      ),
            ),
        [
            order?.available_statuses,
            permissions,
        ],
    );

    const allowedPayments = useMemo(
        () =>
            (
                order?.available_payment_statuses ??
                []
            ).filter((status) =>
                status === "refunded"
                    ? permissions.includes(
                          "orders.refund",
                      )
                    : permissions.includes(
                          "orders.update",
                      ),
            ),
        [
            order?.available_payment_statuses,
            permissions,
        ],
    );

    if (!Number.isFinite(orderId)) {
        return (
            <Navigate
                to="/orders"
                replace
            />
        );
    }

    if (orderQuery.isLoading) {
        return (
            <div className="space-y-6">
                <div className="h-16 w-64 animate-pulse rounded-xl bg-slate-200" />

                <div className="h-96 animate-pulse rounded-xl border border-slate-200 bg-white" />
            </div>
        );
    }

    if (
        orderQuery.isError ||
        !order
    ) {
        return (
            <div className="rounded-xl border border-red-200 bg-white p-10 text-center">
                <AlertCircle
                    size={28}
                    className="mx-auto text-red-500"
                />

                <p className="mt-3 text-sm font-medium text-red-700">
                    Order could not be loaded.
                </p>

                <button
                    type="button"
                    onClick={() =>
                        void orderQuery.refetch()
                    }
                    className="mt-4 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Try again
                </button>
            </div>
        );
    }

    const customerName =
        order.customer?.display_name ||
        order.customer?.name ||
        "Guest customer";

    const paymentMethodName =
        order.latest_payment?.method_name ||
        formatPaymentMethod(
            order.payment_method,
        );

    const paymentProofUrl =
        order.latest_payment
            ?.proof_image_url ?? null;

    const paymentAmount =
        order.latest_payment?.amount ??
        order.grand_total;

    const paymentSubmissionStatus =
        order.latest_payment?.status ??
        null;

    const paymentReference =
        order.latest_payment
            ?.reference_number ?? null;

    const hasActionPanels =
        allowedStatuses.length > 0 ||
        allowedPayments.length > 0;

    const handleError = (
        caught: unknown,
    ) => {
        setError(
            axios.isAxiosError(caught)
                ? caught.response?.data
                      ?.message ??
                      "Unable to update order."
                : "Unable to update order.",
        );
    };

    const handleUpdateStatus =
        async (): Promise<void> => {
            if (!nextStatus) {
                return;
            }

            setError(null);

            try {
                await statusMutation.mutateAsync({
                    status: nextStatus,
                    note,
                });

                setNextStatus("");
                setNote("");
            } catch (caught) {
                handleError(caught);
            }
        };

    const handleUpdatePayment =
        async (): Promise<void> => {
            if (!nextPayment) {
                return;
            }

            setError(null);

            try {
                await paymentMutation.mutateAsync(
                    nextPayment,
                );

                setNextPayment("");
            } catch (caught) {
                handleError(caught);
            }
        };

    const actionPanels = (
        <OrderActionPanels
            orderStatus={order.status}
            paymentStatus={
                order.payment_status
            }
            allowedStatuses={
                allowedStatuses
            }
            allowedPayments={
                allowedPayments
            }
            nextStatus={nextStatus}
            nextPayment={nextPayment}
            note={note}
            statusPending={
                statusMutation.isPending
            }
            paymentPending={
                paymentMutation.isPending
            }
            onStatusChange={
                setNextStatus
            }
            onPaymentChange={
                setNextPayment
            }
            onNoteChange={setNote}
            onUpdateStatus={
                handleUpdateStatus
            }
            onUpdatePayment={
                handleUpdatePayment
            }
        />
    );

    const latestHistory =
        order.status_histories?.[0] ??
        null;

    const latestStatusPanel = (
        <div className="space-y-4">
            <section className="rounded-xl border border-slate-200 bg-white p-5">
                <h2 className="font-semibold text-slate-900">
                    Latest status
                </h2>

                <div className="mt-4 space-y-4">
                    <div className="flex items-center justify-between gap-4">
                        <span className="text-sm text-slate-500">
                            Order
                        </span>

                        <OrderStatusBadge
                            status={order.status}
                        />
                    </div>

                    <div className="flex items-center justify-between gap-4">
                        <span className="text-sm text-slate-500">
                            Payment
                        </span>

                        <OrderStatusBadge
                            status={
                                order.payment_status
                            }
                        />
                    </div>

                    {paymentSubmissionStatus ? (
                        <div className="flex items-start justify-between gap-4">
                            <span className="text-sm text-slate-500">
                                Submission
                            </span>

                            <span className="text-right text-sm font-medium text-slate-800">
                                {formatStatus(
                                    paymentSubmissionStatus,
                                )}
                            </span>
                        </div>
                    ) : null}
                </div>
            </section>

            <section className="rounded-xl border border-slate-200 bg-white p-5">
                <h2 className="font-semibold text-slate-900">
                    Status timeline
                </h2>

                <div className="mt-4">
                    {latestHistory ? (
                        <div className="relative border-l-2 border-slate-200 pl-4">
                            <span className="absolute -left-[5px] top-1 h-2 w-2 rounded-full bg-blue-500 ring-4 ring-white" />

                            <OrderStatusBadge
                                status={
                                    latestHistory.to_status
                                }
                            />

                            <p className="mt-2 text-xs text-slate-500">
                                {formatDate(
                                    latestHistory.created_at,
                                )}
                            </p>

                            <p className="mt-1 text-xs text-slate-500">
                                Updated by{" "}
                                {latestHistory.user
                                    ?.display_name ||
                                    latestHistory.user
                                        ?.name ||
                                    "System"}
                            </p>

                            {latestHistory.note ? (
                                <p className="mt-2 text-sm leading-6 text-slate-700">
                                    {
                                        latestHistory.note
                                    }
                                </p>
                            ) : null}
                        </div>
                    ) : (
                        <div>
                            <OrderStatusBadge
                                status={order.status}
                            />

                            <p className="mt-2 text-xs text-slate-500">
                                Created{" "}
                                {formatDate(
                                    order.created_at,
                                )}
                            </p>

                            <p className="mt-2 text-sm text-slate-600">
                                No status changes have
                                been recorded yet.
                            </p>
                        </div>
                    )}
                </div>
            </section>

            <section className="rounded-xl border border-slate-200 bg-white p-5">
                <h2 className="font-semibold text-slate-900">
                    Order summary
                </h2>

                <div className="mt-4 space-y-3 text-sm">
                    <div className="flex justify-between gap-4">
                        <span className="text-slate-500">
                            Total
                        </span>

                        <span className="font-semibold tabular-nums text-slate-900">
                            {formatMmk(
                                order.grand_total,
                            )}
                        </span>
                    </div>

                    <div className="flex justify-between gap-4">
                        <span className="text-slate-500">
                            Payment method
                        </span>

                        <span className="text-right font-medium text-slate-800">
                            {paymentMethodName}
                        </span>
                    </div>

                    <div className="flex justify-between gap-4">
                        <span className="text-slate-500">
                            Updated
                        </span>

                        <span className="text-right font-medium text-slate-800">
                            {formatDate(
                                order.updated_at,
                            )}
                        </span>
                    </div>
                </div>
            </section>
        </div>
    );

    return (
        <>
            <div className="space-y-6 2xl:pr-[380px]">
                <PageHeader
                    title={
                        order.order_number
                    }
                    description={`Placed ${formatDate(
                        order.created_at,
                    )}`}
                    backLabel="Back to orders"
                    onBack={() =>
                        navigate("/orders")
                    }
                />

                <section className="grid divide-y divide-slate-200 overflow-hidden rounded-xl border border-slate-200 bg-white sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                    <div className="px-5 py-4">
                        <p className="text-xs font-medium uppercase tracking-wide text-slate-500">
                            Order status
                        </p>

                        <div className="mt-2">
                            <OrderStatusBadge
                                status={
                                    order.status
                                }
                            />
                        </div>
                    </div>

                    <div className="px-5 py-4">
                        <p className="text-xs font-medium uppercase tracking-wide text-slate-500">
                            Payment status
                        </p>

                        <div className="mt-2">
                            <OrderStatusBadge
                                status={
                                    order.payment_status
                                }
                            />
                        </div>
                    </div>

                    <div className="px-5 py-4 sm:text-right">
                        <p className="text-xs font-medium uppercase tracking-wide text-slate-500">
                            Order total
                        </p>

                        <p className="mt-1 text-xl font-bold tabular-nums text-slate-900">
                            {formatMmk(
                                order.grand_total,
                            )}
                        </p>
                    </div>
                </section>

                {error ? (
                    <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {error}
                    </div>
                ) : null}

                <div className="space-y-6">
                    <DetailCard title="Order items">
                        <div className="divide-y divide-slate-100">
                            {(order.items ?? [])
                                .length === 0 ? (
                                <div className="p-5 text-sm text-slate-500">
                                    No order items.
                                </div>
                            ) : (
                                (
                                    order.items ??
                                    []
                                ).map(
                                    (item) => (
                                        <div
                                            key={
                                                item.id
                                            }
                                            className="grid grid-cols-[56px_minmax(0,1fr)] items-center gap-x-4 gap-y-2 p-5 sm:grid-cols-[56px_minmax(0,1fr)_auto]"
                                        >
                                            {item
                                                .product
                                                ?.image_url ? (
                                                <img
                                                    src={
                                                        item
                                                            .product
                                                            .image_url
                                                    }
                                                    alt={
                                                        item.product_name
                                                    }
                                                    className="h-14 w-14 rounded-lg border border-slate-200 object-cover"
                                                />
                                            ) : (
                                                <div className="flex h-14 w-14 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                                                    <ImageIcon
                                                        size={
                                                            20
                                                        }
                                                    />
                                                </div>
                                            )}

                                            <div className="min-w-0">
                                                <p className="truncate font-semibold text-slate-900">
                                                    {
                                                        item.product_name
                                                    }
                                                </p>

                                                <p className="mt-1 text-xs text-slate-500">
                                                    SKU{" "}
                                                    {item.sku ||
                                                        "N/A"}{" "}
                                                    ·{" "}
                                                    {formatMmk(
                                                        item.unit_price,
                                                    )}{" "}
                                                    ×{" "}
                                                    {
                                                        item.quantity
                                                    }
                                                </p>
                                            </div>

                                            <p className="col-start-2 font-semibold tabular-nums text-slate-900 sm:col-start-auto sm:text-right">
                                                {formatMmk(
                                                    item.line_total,
                                                )}
                                            </p>
                                        </div>
                                    ),
                                )
                            )}
                        </div>

                        <div className="space-y-2 border-t border-slate-200 bg-slate-50 p-5 text-sm">
                            <div className="flex justify-between text-slate-600">
                                <span>
                                    Subtotal
                                </span>

                                <span className="tabular-nums">
                                    {formatMmk(
                                        order.subtotal,
                                    )}
                                </span>
                            </div>

                            <div className="flex justify-between text-slate-600">
                                <span>
                                    Discount
                                </span>

                                <span className="tabular-nums">
                                    -
                                    {formatMmk(
                                        order.discount_total,
                                    )}
                                </span>
                            </div>

                            <div className="flex justify-between text-slate-600">
                                <span>
                                    Shipping
                                </span>

                                <span className="tabular-nums">
                                    {formatMmk(
                                        order.shipping_total,
                                    )}
                                </span>
                            </div>

                            <div className="flex justify-between text-slate-600">
                                <span>
                                    Tax
                                </span>

                                <span className="tabular-nums">
                                    {formatMmk(
                                        order.tax_total,
                                    )}
                                </span>
                            </div>

                            <div className="flex justify-between border-t border-slate-200 pt-3 text-base font-bold text-slate-900">
                                <span>
                                    Total
                                </span>

                                <span className="tabular-nums">
                                    {formatMmk(
                                        order.grand_total,
                                    )}
                                </span>
                            </div>
                        </div>
                    </DetailCard>

                    <div className="grid gap-6 md:grid-cols-2">
                        <AddressCard
                            title="Shipping address"
                            address={
                                order.shipping_address
                            }
                        />

                        <AddressCard
                            title="Billing address"
                            address={
                                order.billing_address
                            }
                        />
                    </div>

                    {order.notes ? (
                        <section className="rounded-xl border border-slate-200 bg-white p-5">
                            <div className="flex items-center gap-2">
                                <Package
                                    size={18}
                                    className="text-slate-400"
                                />

                                <h2 className="font-semibold text-slate-900">
                                    Order notes
                                </h2>
                            </div>

                            <p className="mt-3 whitespace-pre-wrap text-sm leading-6 text-slate-600">
                                {order.notes}
                            </p>
                        </section>
                    ) : null}

                    <div className="grid gap-6 lg:grid-cols-3">
                        <section className="rounded-xl border border-slate-200 bg-white p-5">
                            <div className="flex items-center gap-2">
                                <UserRound
                                    size={18}
                                    className="text-slate-400"
                                />

                                <h2 className="font-semibold text-slate-900">
                                    Customer
                                </h2>
                            </div>

                            <div className="mt-4">
                                <p className="font-semibold text-slate-800">
                                    {customerName}
                                </p>

                                <p className="mt-1 break-words text-sm text-slate-500">
                                    {order.customer
                                        ?.email ??
                                        "No email"}
                                </p>

                                <p className="mt-1 text-sm text-slate-500">
                                    {order.customer
                                        ?.phone ??
                                        "No phone number"}
                                </p>
                            </div>

                            {order.customer ? (
                                <button
                                    type="button"
                                    onClick={() =>
                                        navigate(
                                            `/customers/${order.customer?.id}`,
                                        )
                                    }
                                    className="mt-4 text-sm font-semibold text-blue-600 hover:text-blue-700"
                                >
                                    View customer
                                </button>
                            ) : null}
                        </section>

                        <section className="rounded-xl border border-slate-200 bg-white p-5">
                            <div className="flex items-center gap-2">
                                <ReceiptText
                                    size={18}
                                    className="text-slate-400"
                                />

                                <h2 className="font-semibold text-slate-900">
                                    Order information
                                </h2>
                            </div>

                            <div className="mt-4 space-y-3 text-sm">
                                <div className="flex justify-between gap-4">
                                    <span className="text-slate-500">
                                        Order ID
                                    </span>

                                    <span className="font-medium text-slate-800">
                                        #{order.id}
                                    </span>
                                </div>

                                <div className="flex justify-between gap-4">
                                    <span className="text-slate-500">
                                        Order number
                                    </span>

                                    <span className="break-all text-right font-medium text-slate-800">
                                        {
                                            order.order_number
                                        }
                                    </span>
                                </div>

                                <div className="flex justify-between gap-4">
                                    <span className="text-slate-500">
                                        Created
                                    </span>

                                    <span className="text-right font-medium text-slate-800">
                                        {formatDate(
                                            order.created_at,
                                        )}
                                    </span>
                                </div>

                                <div className="flex justify-between gap-4">
                                    <span className="text-slate-500">
                                        Updated
                                    </span>

                                    <span className="text-right font-medium text-slate-800">
                                        {formatDate(
                                            order.updated_at,
                                        )}
                                    </span>
                                </div>
                            </div>
                        </section>

                        <section className="rounded-xl border border-slate-200 bg-white p-5">
                            <div className="flex items-center gap-2">
                                <CreditCard
                                    size={18}
                                    className="text-slate-400"
                                />

                                <h2 className="font-semibold text-slate-900">
                                    Payment information
                                </h2>
                            </div>

                            <div className="mt-4 space-y-3 text-sm">
                                <div className="flex justify-between gap-4">
                                    <span className="text-slate-500">
                                        Method
                                    </span>

                                    <span className="font-medium text-slate-800">
                                        {
                                            paymentMethodName
                                        }
                                    </span>
                                </div>

                                <div className="flex items-center justify-between gap-4">
                                    <span className="text-slate-500">
                                        Status
                                    </span>

                                    <OrderStatusBadge
                                        status={
                                            order.payment_status
                                        }
                                    />
                                </div>

                                {paymentSubmissionStatus ? (
                                    <div className="flex justify-between gap-4">
                                        <span className="text-slate-500">
                                            Submission
                                        </span>

                                        <span className="text-right font-medium text-slate-800">
                                            {formatStatus(
                                                paymentSubmissionStatus,
                                            )}
                                        </span>
                                    </div>
                                ) : null}

                                {paymentReference ? (
                                    <div className="flex justify-between gap-4">
                                        <span className="text-slate-500">
                                            Reference
                                        </span>

                                        <span className="break-all text-right font-medium text-slate-800">
                                            {
                                                paymentReference
                                            }
                                        </span>
                                    </div>
                                ) : null}

                                <div className="flex justify-between gap-4">
                                    <span className="text-slate-500">
                                        Amount
                                    </span>

                                    <span className="font-semibold tabular-nums text-slate-900">
                                        {formatMmk(
                                            paymentAmount,
                                        )}
                                    </span>
                                </div>
                            </div>
                        </section>
                    </div>

                    <section className="rounded-xl border border-slate-200 bg-white p-5">
                        <div className="flex items-center justify-between gap-4">
                            <div className="flex items-center gap-2">
                                <ImageIcon
                                    size={18}
                                    className="text-slate-400"
                                />

                                <h2 className="font-semibold text-slate-900">
                                    Payment proof
                                </h2>
                            </div>

                            {paymentProofUrl ? (
                                <a
                                    href={
                                        paymentProofUrl
                                    }
                                    target="_blank"
                                    rel="noreferrer"
                                    className="text-sm font-semibold text-blue-600 hover:text-blue-700"
                                >
                                    View full image
                                </a>
                            ) : null}
                        </div>

                        {paymentProofUrl ? (
                            <a
                                href={
                                    paymentProofUrl
                                }
                                target="_blank"
                                rel="noreferrer"
                                className="mt-4 block overflow-hidden rounded-lg border border-slate-200 bg-slate-50"
                            >
                                <img
                                    src={
                                        paymentProofUrl
                                    }
                                    alt={`Payment proof for ${order.order_number}`}
                                    className="max-h-[420px] w-full object-contain"
                                />
                            </a>
                        ) : (
                            <div className="mt-4 flex min-h-32 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50">
                                <div className="px-4 text-center">
                                    <ImageIcon
                                        size={24}
                                        className="mx-auto text-slate-400"
                                    />

                                    <p className="mt-2 text-sm text-slate-500">
                                        No payment proof uploaded.
                                    </p>
                                </div>
                            </div>
                        )}
                    </section>

                    <section className="rounded-xl border border-slate-200 bg-white p-5">
                        <h2 className="font-semibold text-slate-900">
                            Status history
                        </h2>

                        <div className="mt-4 space-y-4">
                            {(
                                order.status_histories ??
                                []
                            ).length === 0 ? (
                                <p className="text-sm text-slate-500">
                                    No status changes yet.
                                </p>
                            ) : (
                                (
                                    order.status_histories ??
                                    []
                                ).map(
                                    (
                                        history,
                                    ) => (
                                        <div
                                            key={
                                                history.id
                                            }
                                            className="relative border-l-2 border-slate-200 pb-1 pl-4 last:pb-0"
                                        >
                                            <span className="absolute -left-[5px] top-1 h-2 w-2 rounded-full bg-blue-500 ring-4 ring-white" />

                                            <OrderStatusBadge
                                                status={
                                                    history.to_status
                                                }
                                            />

                                            <p className="mt-2 text-xs text-slate-500">
                                                {formatDate(
                                                    history.created_at,
                                                )}{" "}
                                                ·{" "}
                                                {history.user
                                                    ?.display_name ||
                                                    history.user
                                                        ?.name ||
                                                    "System"}
                                            </p>

                                            {history.note ? (
                                                <p className="mt-1 text-sm leading-6 text-slate-700">
                                                    {
                                                        history.note
                                                    }
                                                </p>
                                            ) : null}
                                        </div>
                                    ),
                                )
                            )}
                        </div>
                    </section>

                    <div className="2xl:hidden">
                        {hasActionPanels
                            ? actionPanels
                            : latestStatusPanel}
                    </div>
                </div>
            </div>

            <div className="pointer-events-none fixed right-6 top-1/2 z-40 hidden w-[340px] -translate-y-1/2 2xl:block">
                <div className="pointer-events-auto max-h-[calc(100vh-48px)] overflow-y-auto rounded-2xl border border-slate-200 bg-slate-50 p-3 shadow-xl">
                    {hasActionPanels
                        ? actionPanels
                        : latestStatusPanel}
                </div>
            </div>
        </>
    );
}