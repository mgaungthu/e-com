import {
    ArrowLeft,
    CalendarDays,
    CheckCircle2,
    Clock3,
    CreditCard,
    FileText,
    Laptop,
    Mail,
    MapPin,
    Package,
    Phone,
    Pin,
    ShieldCheck,
    ShoppingBag,
    Smartphone,
    Star,
    UserRound,
    WalletCards,
} from "lucide-react";
import { useNavigate, useParams } from "react-router-dom";

import { CustomerStatusBadge } from "@/features/customers/components/CustomerStatusBadge";
import { useCustomer } from "@/features/customers/hooks/useCustomer";
import type {
    Customer,
    CustomerAddress,
    CustomerDevice,
    CustomerNote,
    CustomerPreference,
    CustomerProfile,
} from "@/features/customers/types/customer.types";

function getCustomerName(customer: Customer): string {
    const fullName = [
        customer.first_name,
        customer.last_name,
    ]
        .filter(Boolean)
        .join(" ")
        .trim();

    return (
        customer.display_name ||
        fullName ||
        customer.name ||
        "Unnamed customer"
    );
}

function getCustomerInitials(customer: Customer): string {
    return getCustomerName(customer)
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join("");
}

function formatDate(
    value?: string | null,
    fallback = "Not available",
): string {
    if (!value) {
        return fallback;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return fallback;
    }

    return new Intl.DateTimeFormat("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    }).format(date);
}

function formatDateTime(
    value?: string | null,
    fallback = "Not available",
): string {
    if (!value) {
        return fallback;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return fallback;
    }

    return new Intl.DateTimeFormat("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "numeric",
        minute: "2-digit",
    }).format(date);
}

function formatCurrency(
    value?: string | number | null,
): string {
    const amount = Number(value ?? 0);

    if (!Number.isFinite(amount)) {
        return "$0.00";
    }

    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(amount);
}

function getProfile(
    customer: Customer,
): CustomerProfile | null {
    return (
        customer.customer_profile ??
        customer.customerProfile ??
        null
    );
}

function getNotes(
    customer: Customer,
): CustomerNote[] {
    return (
        customer.customer_notes ??
        customer.customerNotes ??
        []
    );
}

function getAddressName(
    address: CustomerAddress,
): string {
    const name = [
        address.first_name,
        address.last_name,
    ]
        .filter(Boolean)
        .join(" ")
        .trim();

    return name || "Customer";
}

function getAddressLines(
    address: CustomerAddress,
): string[] {
    const locality = [
        address.city,
        address.state,
        address.postal_code,
    ]
        .filter(Boolean)
        .join(", ");

    return [
        address.company,
        address.address_line_1,
        address.address_line_2,
        locality,
        address.country,
    ].filter(
        (line): line is string =>
            Boolean(line?.trim()),
    );
}

function getDeviceIcon(
    device: CustomerDevice,
) {
    const platform =
        device.platform?.toLowerCase() ?? "";

    if (
        platform.includes("ios") ||
        platform.includes("android") ||
        platform.includes("mobile")
    ) {
        return Smartphone;
    }

    return Laptop;
}

function DetailCard({
    title,
    description,
    children,
}: {
    title: string;
    description?: string;
    children: React.ReactNode;
}) {
    return (
        <section className="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div className="border-b border-slate-200 px-5 py-4">
                <h2 className="font-semibold text-slate-900">
                    {title}
                </h2>

                {description ? (
                    <p className="mt-1 text-sm text-slate-500">
                        {description}
                    </p>
                ) : null}
            </div>

            <div className="p-5">{children}</div>
        </section>
    );
}

function InformationRow({
    icon: Icon,
    label,
    value,
}: {
    icon: React.ComponentType<{
        size?: number;
        className?: string;
    }>;
    label: string;
    value: React.ReactNode;
}) {
    return (
        <div className="flex gap-3">
            <div className="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                <Icon size={17} />
            </div>

            <div className="min-w-0">
                <p className="text-xs font-medium uppercase tracking-wide text-slate-400">
                    {label}
                </p>

                <div className="mt-1 break-words text-sm font-medium text-slate-800">
                    {value}
                </div>
            </div>
        </div>
    );
}

function StatCard({
    icon: Icon,
    label,
    value,
    helper,
}: {
    icon: React.ComponentType<{
        size?: number;
        className?: string;
    }>;
    label: string;
    value: string | number;
    helper?: string;
}) {
    return (
        <div className="rounded-xl border border-slate-200 bg-white p-5">
            <div className="flex items-start justify-between gap-4">
                <div>
                    <p className="text-sm font-medium text-slate-500">
                        {label}
                    </p>

                    <p className="mt-2 text-2xl font-bold text-slate-900">
                        {value}
                    </p>

                    {helper ? (
                        <p className="mt-1 text-xs text-slate-500">
                            {helper}
                        </p>
                    ) : null}
                </div>

                <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <Icon size={21} />
                </div>
            </div>
        </div>
    );
}

function AddressCard({
    address,
}: {
    address: CustomerAddress;
}) {
    const addressLines =
        getAddressLines(address);

    return (
        <article className="rounded-xl border border-slate-200 p-4">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div className="flex flex-wrap items-center gap-2">
                        <h3 className="font-semibold text-slate-900">
                            {getAddressName(address)}
                        </h3>

                        {address.type ? (
                            <span className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium capitalize text-slate-600">
                                {address.type}
                            </span>
                        ) : null}
                    </div>

                    <div className="mt-3 space-y-1 text-sm text-slate-600">
                        {addressLines.map((line) => (
                            <p key={line}>{line}</p>
                        ))}
                    </div>

                    {address.phone ? (
                        <p className="mt-3 flex items-center gap-2 text-sm text-slate-600">
                            <Phone size={15} />
                            {address.phone}
                        </p>
                    ) : null}
                </div>

                <div className="flex flex-wrap gap-2">
                    {address.is_default_shipping ? (
                        <span className="inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                            <Package size={13} />
                            Default shipping
                        </span>
                    ) : null}

                    {address.is_default_billing ? (
                        <span className="inline-flex items-center gap-1.5 rounded-full border border-violet-200 bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700">
                            <CreditCard size={13} />
                            Default billing
                        </span>
                    ) : null}
                </div>
            </div>
        </article>
    );
}

function NoteCard({
    note,
}: {
    note: CustomerNote;
}) {
    const creatorName =
        note.creator?.display_name ||
        [
            note.creator?.first_name,
            note.creator?.last_name,
        ]
            .filter(Boolean)
            .join(" ")
            .trim() ||
        note.creator?.name ||
        "Admin";

    return (
        <article className="rounded-xl border border-slate-200 p-4">
            <div className="flex items-start justify-between gap-4">
                <div className="min-w-0">
                    <div className="flex flex-wrap items-center gap-2">
                        <p className="text-sm font-semibold text-slate-900">
                            {creatorName}
                        </p>

                        {note.is_pinned ? (
                            <span className="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                <Pin size={12} />
                                Pinned
                            </span>
                        ) : null}
                    </div>

                    <p className="mt-3 whitespace-pre-wrap text-sm leading-6 text-slate-700">
                        {note.note}
                    </p>
                </div>

                <time className="shrink-0 text-xs text-slate-400">
                    {formatDate(note.created_at)}
                </time>
            </div>
        </article>
    );
}

function DeviceCard({
    device,
}: {
    device: CustomerDevice;
}) {
    const DeviceIcon = getDeviceIcon(device);

    return (
        <article className="flex items-start gap-3 rounded-xl border border-slate-200 p-4">
            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                <DeviceIcon size={19} />
            </div>

            <div className="min-w-0">
                <p className="font-semibold text-slate-900">
                    {device.device_name ||
                        device.platform ||
                        "Unknown device"}
                </p>

                <div className="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500">
                    {device.platform ? (
                        <span className="capitalize">
                            {device.platform}
                        </span>
                    ) : null}

                    {device.app_version ? (
                        <span>
                            App {device.app_version}
                        </span>
                    ) : null}

                    <span>
                        Last used{" "}
                        {formatDateTime(
                            device.last_used_at,
                        )}
                    </span>
                </div>
            </div>
        </article>
    );
}

function PreferenceContent({
    preference,
}: {
    preference?: CustomerPreference | null;
}) {
    if (!preference) {
        return (
            <div className="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                <WalletCards
                    size={24}
                    className="mx-auto text-slate-400"
                />

                <p className="mt-3 text-sm font-medium text-slate-700">
                    No preferences available
                </p>

                <p className="mt-1 text-xs text-slate-500">
                    This customer has not configured
                    preferences yet.
                </p>
            </div>
        );
    }

    return (
        <div className="grid gap-4 sm:grid-cols-2">
            <InformationRow
                icon={WalletCards}
                label="Currency"
                value={
                    preference.currency ??
                    "Not configured"
                }
            />

            <InformationRow
                icon={ShieldCheck}
                label="Language"
                value={
                    preference.language ??
                    "Not configured"
                }
            />

            <InformationRow
                icon={Mail}
                label="Marketing email"
                value={
                    preference.marketing_email
                        ? "Enabled"
                        : "Disabled"
                }
            />

            <InformationRow
                icon={Phone}
                label="Marketing SMS"
                value={
                    preference.marketing_sms
                        ? "Enabled"
                        : "Disabled"
                }
            />
        </div>
    );
}

export default function CustomerDetailPage() {
    const navigate = useNavigate();

    const { customerId } = useParams<{
        customerId: string;
    }>();

    const customerQuery =
        useCustomer(customerId);

    if (customerQuery.isLoading) {
        return (
            <div className="space-y-6">
                <div className="h-10 w-48 animate-pulse rounded-lg bg-slate-200" />

                <div className="rounded-xl border border-slate-200 bg-white p-8">
                    <div className="animate-pulse space-y-5">
                        <div className="h-20 rounded-xl bg-slate-100" />
                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="h-28 rounded-xl bg-slate-100" />
                            <div className="h-28 rounded-xl bg-slate-100" />
                            <div className="h-28 rounded-xl bg-slate-100" />
                        </div>
                        <div className="h-64 rounded-xl bg-slate-100" />
                    </div>
                </div>
            </div>
        );
    }

    if (
        customerQuery.isError ||
        !customerQuery.data?.data.customer
    ) {
        return (
            <div className="space-y-6">
                <button
                    type="button"
                    onClick={() =>
                        navigate("/customers")
                    }
                    className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 transition hover:text-blue-600"
                >
                    <ArrowLeft size={17} />
                    Back to customers
                </button>

                <div className="rounded-xl border border-red-200 bg-red-50 p-8 text-center">
                    <p className="font-semibold text-red-700">
                        Unable to load customer details.
                    </p>

                    <p className="mt-1 text-sm text-red-600">
                        The customer may not exist, or
                        the request could not be
                        completed.
                    </p>

                    <button
                        type="button"
                        onClick={() =>
                            customerQuery.refetch()
                        }
                        className="mt-4 rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100"
                    >
                        Try again
                    </button>
                </div>
            </div>
        );
    }

    const customer =
        customerQuery.data.data.customer;

    const profile = getProfile(customer);
    const addresses = customer.addresses ?? [];
    const notes = getNotes(customer);
    const devices = customer.devices ?? [];
    const preference = customer.preference ?? null;

    return (
        <div className="space-y-6">
            <button
                type="button"
                onClick={() =>
                    navigate("/customers")
                }
                className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 transition hover:text-blue-600"
            >
                <ArrowLeft size={17} />
                Back to customers
            </button>

            <section className="rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
                <div className="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                    <div className="flex min-w-0 items-center gap-4">
                        <div className="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-50 text-lg font-bold text-blue-700">
                            {customer.avatar_url ? (
                                <img
                                    src={customer.avatar_url}
                                    alt={getCustomerName(
                                        customer,
                                    )}
                                    className="h-full w-full object-cover"
                                />
                            ) : (
                                getCustomerInitials(
                                    customer,
                                ) || (
                                    <UserRound
                                        size={25}
                                    />
                                )
                            )}
                        </div>

                        <div className="min-w-0">
                            <div className="flex flex-wrap items-center gap-2">
                                <h1 className="truncate text-2xl font-bold text-slate-900">
                                    {getCustomerName(
                                        customer,
                                    )}
                                </h1>

                                <CustomerStatusBadge
                                    status={
                                        customer.status
                                    }
                                />
                            </div>

                            <p className="mt-1 text-sm text-slate-500">
                                Customer ID #{customer.id}
                            </p>

                            <div className="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-sm text-slate-600">
                                <span className="inline-flex items-center gap-1.5">
                                    <Mail size={15} />
                                    {customer.email}
                                </span>

                                {customer.phone ? (
                                    <span className="inline-flex items-center gap-1.5">
                                        <Phone
                                            size={15}
                                        />
                                        {customer.phone}
                                    </span>
                                ) : null}
                            </div>
                        </div>
                    </div>

                    <div className="text-left sm:text-right">
                        <p className="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Member since
                        </p>

                        <p className="mt-1 text-sm font-semibold text-slate-700">
                            {formatDate(
                                customer.created_at,
                            )}
                        </p>
                    </div>
                </div>
            </section>

            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <StatCard
                    icon={ShoppingBag}
                    label="Total orders"
                    value={profile?.total_orders ?? 0}
                    helper="Completed customer orders"
                />

                <StatCard
                    icon={WalletCards}
                    label="Total spent"
                    value={formatCurrency(
                        profile?.total_spent,
                    )}
                    helper="Lifetime customer value"
                />

                <StatCard
                    icon={Star}
                    label="Loyalty points"
                    value={
                        profile?.loyalty_points ?? 0
                    }
                    helper="Available reward points"
                />

                <StatCard
                    icon={Clock3}
                    label="Last order"
                    value={formatDate(
                        profile?.last_order_at,
                        "No orders",
                    )}
                    helper="Most recent order date"
                />
            </div>

            <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                <div className="space-y-6">
                    <DetailCard
                        title="Customer information"
                        description="Account, verification, and recent activity details."
                    >
                        <div className="grid gap-5 sm:grid-cols-2">
                            <InformationRow
                                icon={UserRound}
                                label="Full name"
                                value={getCustomerName(
                                    customer,
                                )}
                            />

                            <InformationRow
                                icon={Mail}
                                label="Email address"
                                value={
                                    <span className="inline-flex flex-wrap items-center gap-2">
                                        {customer.email}

                                        {customer.email_verified_at ? (
                                            <CheckCircle2
                                                size={15}
                                                className="text-emerald-500"
                                            />
                                        ) : null}
                                    </span>
                                }
                            />

                            <InformationRow
                                icon={Phone}
                                label="Phone number"
                                value={
                                    <span className="inline-flex flex-wrap items-center gap-2">
                                        {customer.phone ??
                                            "Not provided"}

                                        {customer.phone_verified_at ? (
                                            <CheckCircle2
                                                size={15}
                                                className="text-emerald-500"
                                            />
                                        ) : null}
                                    </span>
                                }
                            />

                            <InformationRow
                                icon={ShieldCheck}
                                label="Account status"
                                value={
                                    <CustomerStatusBadge
                                        status={
                                            customer.status
                                        }
                                    />
                                }
                            />

                            <InformationRow
                                icon={Clock3}
                                label="Last login"
                                value={formatDateTime(
                                    customer.last_login_at,
                                )}
                            />

                            <InformationRow
                                icon={Laptop}
                                label="Last login IP"
                                value={
                                    customer.last_login_ip ??
                                    "Not available"
                                }
                            />

                            <InformationRow
                                icon={CalendarDays}
                                label="Created"
                                value={formatDateTime(
                                    customer.created_at,
                                )}
                            />

                            <InformationRow
                                icon={CalendarDays}
                                label="Last updated"
                                value={formatDateTime(
                                    customer.updated_at,
                                )}
                            />
                        </div>
                    </DetailCard>

                    <DetailCard
                        title="Addresses"
                        description={`${addresses.length} saved ${
                            addresses.length === 1
                                ? "address"
                                : "addresses"
                        }`}
                    >
                        {addresses.length === 0 ? (
                            <div className="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                                <MapPin
                                    size={25}
                                    className="mx-auto text-slate-400"
                                />

                                <p className="mt-3 text-sm font-medium text-slate-700">
                                    No saved addresses
                                </p>

                                <p className="mt-1 text-xs text-slate-500">
                                    This customer has not
                                    added a shipping or
                                    billing address.
                                </p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {addresses.map(
                                    (address) => (
                                        <AddressCard
                                            key={
                                                address.id
                                            }
                                            address={
                                                address
                                            }
                                        />
                                    ),
                                )}
                            </div>
                        )}
                    </DetailCard>

                    <DetailCard
                        title="Internal notes"
                        description="Private notes visible only to administrators."
                    >
                        {notes.length === 0 ? (
                            <div className="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                                <FileText
                                    size={25}
                                    className="mx-auto text-slate-400"
                                />

                                <p className="mt-3 text-sm font-medium text-slate-700">
                                    No customer notes
                                </p>

                                <p className="mt-1 text-xs text-slate-500">
                                    Internal notes will
                                    appear here.
                                </p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {notes.map((note) => (
                                    <NoteCard
                                        key={note.id}
                                        note={note}
                                    />
                                ))}
                            </div>
                        )}
                    </DetailCard>
                </div>

                <div className="space-y-6">
                    <DetailCard
                        title="Account summary"
                        description="Quick account information."
                    >
                        <div className="space-y-5">
                            <InformationRow
                                icon={MapPin}
                                label="Saved addresses"
                                value={
                                    customer.addresses_count ??
                                    addresses.length
                                }
                            />

                            <InformationRow
                                icon={ShoppingBag}
                                label="Orders"
                                value={
                                    profile?.total_orders ??
                                    0
                                }
                            />

                            <InformationRow
                                icon={Star}
                                label="Loyalty points"
                                value={
                                    profile?.loyalty_points ??
                                    0
                                }
                            />

                            <InformationRow
                                icon={Clock3}
                                label="Last order"
                                value={formatDate(
                                    profile?.last_order_at,
                                    "No orders",
                                )}
                            />
                        </div>
                    </DetailCard>

                    <DetailCard
                        title="Preferences"
                        description="Store and communication preferences."
                    >
                        <PreferenceContent
                            preference={preference}
                        />
                    </DetailCard>

                    <DetailCard
                        title="Devices"
                        description={`${devices.length} registered ${
                            devices.length === 1
                                ? "device"
                                : "devices"
                        }`}
                    >
                        {devices.length === 0 ? (
                            <div className="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                                <Smartphone
                                    size={25}
                                    className="mx-auto text-slate-400"
                                />

                                <p className="mt-3 text-sm font-medium text-slate-700">
                                    No registered devices
                                </p>

                                <p className="mt-1 text-xs text-slate-500">
                                    Device activity will
                                    appear here.
                                </p>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {devices.map(
                                    (device) => (
                                        <DeviceCard
                                            key={
                                                device.id
                                            }
                                            device={device}
                                        />
                                    ),
                                )}
                            </div>
                        )}
                    </DetailCard>
                </div>
            </div>
        </div>
    );
}