import axios from "axios";

import {
    BellRing,
    CheckCircle2,
    Database,
    LoaderCircle,
    Megaphone,
    PackageCheck,
    PackageSearch,
    Search,
    Send,
    Smartphone,
    Sparkles,
    UsersRound,
    type LucideIcon,
} from "lucide-react";

import {
    type FormEvent,
    useDeferredValue,
    useState,
} from "react";

import PageHeader from "@/components/PageHeader";

import {
    FormActions,
    FormErrorAlert,
    FormField,
    FormSection,
    TextArea,
    TextInput,
} from "@/components/forms";

import {
    buildNotificationTemplate,
    NOTIFICATION_TYPE_OPTIONS,
    notificationRequiresProduct,
} from "@/features/notifications/constants/notification.constants";

import { useSendNotification } from "@/features/notifications/hooks/useNotificationMutations";

import { useNotificationProducts } from "@/features/notifications/hooks/useNotificationProducts";

import type {
    LaravelErrorResponse,
    LaravelValidationErrors,
    NotificationFormValues,
    NotificationProductOption,
    NotificationType,
} from "@/features/notifications/types/notification.types";

import { useAuthStore } from "@/store/authStore";

const INITIAL_VALUES: NotificationFormValues = {
    type: "general",
    product_id: "",
    title: "",
    body: "",
};

type NotificationTypeMeta = {
    description: string;
    icon: LucideIcon;
};

const NOTIFICATION_TYPE_META: Record<
    NotificationType,
    NotificationTypeMeta
> = {
    new_product: {
        description:
            "Introduce a newly available product to your customers.",
        icon: Sparkles,
    },

    product_restocked: {
        description:
            "Let customers know a popular product is available again.",
        icon: PackageCheck,
    },

    general: {
        description:
            "Send news, updates, or announcements to your customers.",
        icon: Megaphone,
    },
};

export default function NotificationsPage() {
    const sendMutation = useSendNotification();

    const canSend = useAuthStore(
        (state) =>
            state.user?.permissions.includes(
                "notifications.send",
            ) ?? false,
    );

    const [values, setValues] =
        useState<NotificationFormValues>(
            INITIAL_VALUES,
        );

    const [
        productSearch,
        setProductSearch,
    ] = useState("");

    const [
        isProductPickerOpen,
        setIsProductPickerOpen,
    ] = useState(false);

    const [
        selectedProduct,
        setSelectedProduct,
    ] =
        useState<NotificationProductOption | null>(
            null,
        );

    const [
        validationErrors,
        setValidationErrors,
    ] =
        useState<LaravelValidationErrors>(
            {},
        );

    const [
        formError,
        setFormError,
    ] =
        useState<string | null>(
            null,
        );

    const [
        successMessage,
        setSuccessMessage,
    ] =
        useState<string | null>(
            null,
        );

    const deferredProductSearch =
        useDeferredValue(
            productSearch,
        );

    const requiresProduct =
        notificationRequiresProduct(
            values.type,
        );

    const productsQuery =
        useNotificationProducts(
            deferredProductSearch,
            requiresProduct,
        );

    const products =
        productsQuery.data?.data
            .products ?? [];

    /*
    |--------------------------------------------------------------------------
    | Keep Selected Product Visible
    |--------------------------------------------------------------------------
    |
    | If the administrator changes the search text after selecting a product,
    | keep the selected product in the picker options.
    |
    */

    const productOptions =
        selectedProduct &&
        !products.some(
            (product) =>
                product.id ===
                selectedProduct.id,
        )
            ? [
                  selectedProduct,
                  ...products,
              ]
            : products;

    const selectedTypeOption =
        NOTIFICATION_TYPE_OPTIONS.find(
            (option) =>
                option.value ===
                values.type,
        );

    function clearMessages() {
        setFormError(
            null,
        );

        setSuccessMessage(
            null,
        );
    }

    function clearFieldError(
        field:
            keyof NotificationFormValues,
    ) {
        setValidationErrors(
            (current) => ({
                ...current,
                [field]: [],
            }),
        );
    }

    function fieldError(
        field:
            keyof NotificationFormValues,
    ): string | null {
        return (
            validationErrors[
                field
            ]?.[0] ?? null
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Notification Type
    |--------------------------------------------------------------------------
    */

    function handleTypeChange(
        type: NotificationType,
    ) {
        const template =
            buildNotificationTemplate(
                type,
            );

        setValues({
            type,
            product_id: "",
            title:
                template.title,
            body:
                template.body,
        });

        setSelectedProduct(
            null,
        );

        setProductSearch(
            "",
        );

        setIsProductPickerOpen(
            false,
        );

        setValidationErrors(
            {},
        );

        clearMessages();
    }

    /*
    |--------------------------------------------------------------------------
    | Product
    |--------------------------------------------------------------------------
    */

    function handleProductSearchChange(
        value: string,
    ) {
        setProductSearch(
            value,
        );

        setIsProductPickerOpen(
            true,
        );

        /*
        |--------------------------------------------------------------------------
        | Clear Previous Selection
        |--------------------------------------------------------------------------
        |
        | Once the administrator changes the search text, the previously
        | selected product is no longer considered selected.
        |
        */

        if (
            selectedProduct &&
            value !==
                selectedProduct.name
        ) {
            const template =
                buildNotificationTemplate(
                    values.type,
                );

            setSelectedProduct(
                null,
            );

            setValues(
                (current) => ({
                    ...current,

                    product_id:
                        "",

                    title:
                        template.title,

                    body:
                        template.body,
                }),
            );
        }

        clearFieldError(
            "product_id",
        );

        clearMessages();
    }

    function handleProductChange(
        productId: string,
    ) {
        const product =
            productOptions.find(
                (item) =>
                    String(
                        item.id,
                    ) ===
                    productId,
            ) ?? null;

        const template =
            buildNotificationTemplate(
                values.type,
                product,
            );

        setSelectedProduct(
            product,
        );

        setValues(
            (current) => ({
                ...current,

                product_id:
                    productId,

                title:
                    template.title,

                body:
                    template.body,
            }),
        );

        if (product) {
            setProductSearch(
                product.name,
            );
        }

        setIsProductPickerOpen(
            false,
        );

        clearFieldError(
            "product_id",
        );

        clearFieldError(
            "title",
        );

        clearFieldError(
            "body",
        );

        clearMessages();
    }

    /*
    |--------------------------------------------------------------------------
    | Editable Content
    |--------------------------------------------------------------------------
    */

    function updateContent(
        field:
            | "title"
            | "body",
        value: string,
    ) {
        setValues(
            (current) => ({
                ...current,
                [field]:
                    value,
            }),
        );

        clearFieldError(
            field,
        );

        clearMessages();
    }

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    async function handleSubmit(
        event:
            FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        setValidationErrors(
            {},
        );

        setFormError(
            null,
        );

        setSuccessMessage(
            null,
        );

        try {
            const response =
                await sendMutation.mutateAsync(
                    values,
                );

            const recipientCount =
                response.data
                    .notification
                    .recipient_count;

            setSuccessMessage(
                `Notification sent successfully to ${recipientCount} customer${
                    recipientCount === 1
                        ? ""
                        : "s"
                }.`,
            );
        } catch (error) {
            if (
                axios.isAxiosError<LaravelErrorResponse>(
                    error,
                )
            ) {
                if (
                    error.response
                        ?.status ===
                    422
                ) {
                    setValidationErrors(
                        error.response
                            .data
                            .errors ??
                            {},
                    );
                }

                setFormError(
                    error.response
                        ?.data
                        .message ??
                        "Unable to send notification.",
                );

                return;
            }

            setFormError(
                "Unable to send notification. Please try again.",
            );
        }
    }

    const submitDisabled =
        !canSend ||
        values.title.trim() ===
            "" ||
        values.body.trim() ===
            "" ||
        (
            requiresProduct &&
            values.product_id ===
                ""
        );

    return (
        <div className="space-y-6 pb-10">
            <PageHeader
                title="Notifications"
                description="Create and send product announcements, restock alerts, and general updates to customers."
            />

            <form
                onSubmit={
                    handleSubmit
                }
                noValidate
            >
                <div className="mb-6 space-y-3">
                    <FormErrorAlert
                        message={
                            formError
                        }
                    />

                    {successMessage ? (
                        <div className="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-emerald-800 shadow-sm">
                            <div className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                                <CheckCircle2
                                    size={
                                        18
                                    }
                                />
                            </div>

                            <div>
                                <p className="text-sm font-semibold">
                                    Notification sent
                                </p>

                                <p className="mt-0.5 text-sm text-emerald-700">
                                    {
                                        successMessage
                                    }
                                </p>
                            </div>
                        </div>
                    ) : null}

                    {!canSend ? (
                        <div className="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            You do not have permission to send customer notifications.
                        </div>
                    ) : null}
                </div>

                <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                    {/*
                    |--------------------------------------------------------------------------
                    | Composer
                    |--------------------------------------------------------------------------
                    */}

                    <div className="min-w-0 space-y-6">
                        {/*
                        |--------------------------------------------------------------------------
                        | Notification Type
                        |--------------------------------------------------------------------------
                        */}

                        <FormSection
                            title="Notification type"
                            description="Choose what kind of message you want to send."
                            icon={
                                <BellRing
                                    size={
                                        20
                                    }
                                />
                            }
                            contentClassName="p-5"
                        >
                            <div className="grid gap-3 md:grid-cols-3">
                                {NOTIFICATION_TYPE_OPTIONS.map(
                                    (
                                        option,
                                    ) => {
                                        const meta =
                                            NOTIFICATION_TYPE_META[
                                                option
                                                    .value
                                            ];

                                        const Icon =
                                            meta.icon;

                                        const isActive =
                                            values.type ===
                                            option.value;

                                        return (
                                            <button
                                                key={
                                                    option.value
                                                }
                                                type="button"
                                                onClick={() =>
                                                    handleTypeChange(
                                                        option.value,
                                                    )
                                                }
                                                disabled={
                                                    sendMutation.isPending ||
                                                    !canSend
                                                }
                                                className={[
                                                    "group rounded-2xl border p-4 text-left transition-all duration-200",
                                                    "disabled:cursor-not-allowed disabled:opacity-60",

                                                    isActive
                                                        ? "border-slate-900 bg-slate-900 text-white shadow-lg shadow-slate-900/10"
                                                        : "border-slate-200 bg-white text-slate-900 hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md",
                                                ].join(
                                                    " ",
                                                )}
                                            >
                                                <div
                                                    className={[
                                                        "mb-4 flex h-10 w-10 items-center justify-center rounded-xl transition-colors",

                                                        isActive
                                                            ? "bg-white/10 text-white"
                                                            : "bg-slate-100 text-slate-700 group-hover:bg-slate-200",
                                                    ].join(
                                                        " ",
                                                    )}
                                                >
                                                    <Icon
                                                        size={
                                                            19
                                                        }
                                                    />
                                                </div>

                                                <p className="text-sm font-semibold">
                                                    {
                                                        option.label
                                                    }
                                                </p>

                                                <p
                                                    className={[
                                                        "mt-1.5 text-xs leading-5",

                                                        isActive
                                                            ? "text-slate-300"
                                                            : "text-slate-500",
                                                    ].join(
                                                        " ",
                                                    )}
                                                >
                                                    {
                                                        meta.description
                                                    }
                                                </p>
                                            </button>
                                        );
                                    },
                                )}
                            </div>

                            {fieldError(
                                "type",
                            ) ? (
                                <p className="mt-3 text-sm text-red-600">
                                    {fieldError(
                                        "type",
                                    )}
                                </p>
                            ) : null}
                        </FormSection>

                        {/*
                        |--------------------------------------------------------------------------
                        | Product
                        |--------------------------------------------------------------------------
                        */}

                        {requiresProduct ? (
                            <FormSection
                                title="Product"
                                description="Search and select the product customers should see when they open the notification."
                                icon={
                                    <PackageSearch
                                        size={
                                            20
                                        }
                                    />
                                }
                                overflowVisible
                                contentClassName="space-y-5 p-5"
                            >
                                <FormField
                                    label="Product"
                                    htmlFor="product_search"
                                    required
                                    helperText="Search by product name or SKU."
                                    error={
                                        fieldError(
                                            "product_id",
                                        )
                                    }
                                >
                                    <div className="relative">
                                        <div className="relative">
                                            <Search
                                                size={
                                                    17
                                                }
                                                className="pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-slate-400"
                                            />

                                            <TextInput
                                                id="product_search"
                                                value={
                                                    productSearch
                                                }
                                                onChange={(
                                                    event,
                                                ) =>
                                                    handleProductSearchChange(
                                                        event
                                                            .target
                                                            .value,
                                                    )
                                                }
                                                onFocus={() =>
                                                    setIsProductPickerOpen(
                                                        true,
                                                    )
                                                }
                                                onBlur={() => {
                                                    window.setTimeout(
                                                        () =>
                                                            setIsProductPickerOpen(
                                                                false,
                                                            ),
                                                        120,
                                                    );
                                                }}
                                                disabled={
                                                    sendMutation.isPending ||
                                                    !canSend
                                                }
                                                placeholder="Search and select product..."
                                                className="pl-10 pr-10"
                                                error={
                                                    fieldError(
                                                        "product_id",
                                                    )
                                                }
                                            />

                                            {productsQuery.isFetching ? (
                                                <LoaderCircle
                                                    size={
                                                        17
                                                    }
                                                    className="absolute right-3 top-1/2 -translate-y-1/2 animate-spin text-slate-400"
                                                />
                                            ) : null}
                                        </div>

                                        {isProductPickerOpen ? (
                                            <div className="absolute left-0 right-0 top-full z-30 mt-2 max-h-72 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                                                {productsQuery.isLoading ? (
                                                    <div className="flex items-center justify-center gap-2 px-4 py-8 text-sm text-slate-500">
                                                        <LoaderCircle
                                                            size={
                                                                17
                                                            }
                                                            className="animate-spin"
                                                        />

                                                        Loading products...
                                                    </div>
                                                ) : productsQuery.isError ? (
                                                    <div className="px-4 py-6 text-center">
                                                        <p className="text-sm text-slate-500">
                                                            Unable to load products.
                                                        </p>

                                                        <button
                                                            type="button"
                                                            onMouseDown={(
                                                                event,
                                                            ) =>
                                                                event.preventDefault()
                                                            }
                                                            onClick={() =>
                                                                void productsQuery.refetch()
                                                            }
                                                            className="mt-2 text-sm font-semibold text-slate-900 underline underline-offset-4"
                                                        >
                                                            Try again
                                                        </button>
                                                    </div>
                                                ) : productOptions.length ===
                                                  0 ? (
                                                    <div className="px-4 py-8 text-center">
                                                        <PackageSearch
                                                            size={
                                                                24
                                                            }
                                                            className="mx-auto text-slate-300"
                                                        />

                                                        <p className="mt-2 text-sm font-medium text-slate-700">
                                                            No products found
                                                        </p>

                                                        <p className="mt-1 text-xs text-slate-500">
                                                            Try another product name or SKU.
                                                        </p>
                                                    </div>
                                                ) : (
                                                    productOptions.map(
                                                        (
                                                            product,
                                                        ) => {
                                                            const isSelected =
                                                                selectedProduct?.id ===
                                                                product.id;

                                                            return (
                                                                <button
                                                                    key={
                                                                        product.id
                                                                    }
                                                                    type="button"
                                                                    onMouseDown={(
                                                                        event,
                                                                    ) =>
                                                                        event.preventDefault()
                                                                    }
                                                                    onClick={() =>
                                                                        handleProductChange(
                                                                            String(
                                                                                product.id,
                                                                            ),
                                                                        )
                                                                    }
                                                                    className={[
                                                                        "flex w-full items-center justify-between gap-4 rounded-xl px-3 py-3 text-left transition-colors",

                                                                        isSelected
                                                                            ? "bg-slate-100"
                                                                            : "hover:bg-slate-50",
                                                                    ].join(
                                                                        " ",
                                                                    )}
                                                                >
                                                                    <div className="min-w-0">
                                                                        <p className="truncate text-sm font-semibold text-slate-900">
                                                                            {
                                                                                product.name
                                                                            }
                                                                        </p>

                                                                        <div className="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                                                            <span>
                                                                                SKU:{" "}
                                                                                {
                                                                                    product.sku
                                                                                }
                                                                            </span>

                                                                            <span>
                                                                                •
                                                                            </span>

                                                                            <span>
                                                                                Stock:{" "}
                                                                                {
                                                                                    product.stock_quantity
                                                                                }
                                                                            </span>
                                                                        </div>
                                                                    </div>

                                                                    <div className="flex shrink-0 items-center gap-2">
                                                                        {!product.is_active ? (
                                                                            <span className="rounded-full bg-amber-50 px-2 py-1 text-[11px] font-medium text-amber-700">
                                                                                Inactive
                                                                            </span>
                                                                        ) : null}

                                                                        {isSelected ? (
                                                                            <CheckCircle2
                                                                                size={
                                                                                    18
                                                                                }
                                                                                className="text-emerald-600"
                                                                            />
                                                                        ) : null}
                                                                    </div>
                                                                </button>
                                                            );
                                                        },
                                                    )
                                                )}
                                            </div>
                                        ) : null}
                                    </div>
                                </FormField>

                                {selectedProduct ? (
                                    <div className="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div className="flex min-w-0 items-center gap-3">
                                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700">
                                                <PackageCheck
                                                    size={
                                                        19
                                                    }
                                                />
                                            </div>

                                            <div className="min-w-0">
                                                <p className="truncate text-sm font-semibold text-slate-900">
                                                    {
                                                        selectedProduct.name
                                                    }
                                                </p>

                                                <p className="mt-0.5 text-xs text-slate-500">
                                                    SKU:{" "}
                                                    {
                                                        selectedProduct.sku
                                                    }
                                                </p>
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-2">
                                            <span className="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600">
                                                Stock{" "}
                                                {
                                                    selectedProduct.stock_quantity
                                                }
                                            </span>

                                            {!selectedProduct.is_active ? (
                                                <span className="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">
                                                    Inactive
                                                </span>
                                            ) : null}
                                        </div>
                                    </div>
                                ) : null}
                            </FormSection>
                        ) : null}

                        {/*
                        |--------------------------------------------------------------------------
                        | Message
                        |--------------------------------------------------------------------------
                        */}

                        <FormSection
                            title="Message"
                            description={
                                requiresProduct
                                    ? "The template is generated automatically from the selected product. You can edit it before sending."
                                    : "Write the announcement customers should receive."
                            }
                            icon={
                                <Send
                                    size={
                                        20
                                    }
                                />
                            }
                            contentClassName="space-y-5 p-5"
                        >
                            {requiresProduct &&
                            selectedProduct ? (
                                <div className="flex items-center gap-2 rounded-xl border border-violet-100 bg-violet-50 px-3.5 py-2.5 text-xs font-medium text-violet-700">
                                    <Sparkles
                                        size={
                                            15
                                        }
                                    />

                                    Suggested template applied. You can edit the content below.
                                </div>
                            ) : null}

                            <FormField
                                label="Title"
                                htmlFor="notification_title"
                                required
                                error={
                                    fieldError(
                                        "title",
                                    )
                                }
                            >
                                <TextInput
                                    id="notification_title"
                                    value={
                                        values.title
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        updateContent(
                                            "title",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    disabled={
                                        sendMutation.isPending ||
                                        !canSend
                                    }
                                    maxLength={
                                        255
                                    }
                                    placeholder="Notification title"
                                    error={
                                        fieldError(
                                            "title",
                                        )
                                    }
                                />
                            </FormField>

                            <FormField
                                label="Message"
                                htmlFor="notification_body"
                                required
                                helperText={`${values.body.length}/1000 characters`}
                                error={
                                    fieldError(
                                        "body",
                                    )
                                }
                            >
                                <TextArea
                                    id="notification_body"
                                    value={
                                        values.body
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        updateContent(
                                            "body",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    disabled={
                                        sendMutation.isPending ||
                                        !canSend
                                    }
                                    rows={
                                        7
                                    }
                                    maxLength={
                                        1000
                                    }
                                    placeholder="Write your notification message..."
                                    error={
                                        fieldError(
                                            "body",
                                        )
                                    }
                                />
                            </FormField>
                        </FormSection>

                        <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <FormActions
                                submitLabel="Send Notification"
                                submittingLabel="Sending..."
                                submitIcon={
                                    <Send
                                        size={
                                            18
                                        }
                                    />
                                }
                                isSubmitting={
                                    sendMutation.isPending
                                }
                                submitDisabled={
                                    submitDisabled
                                }
                            />
                        </div>
                    </div>

                    {/*
                    |--------------------------------------------------------------------------
                    | Preview Sidebar
                    |--------------------------------------------------------------------------
                    */}

                    <aside className="space-y-5 xl:sticky xl:top-6 xl:self-start">
                        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div className="border-b border-slate-100 px-5 py-4">
                                <div className="flex items-center justify-between gap-3">
                                    <div>
                                        <p className="text-sm font-semibold text-slate-900">
                                            Live preview
                                        </p>

                                        <p className="mt-0.5 text-xs text-slate-500">
                                            Customer push notification
                                        </p>
                                    </div>

                                    <span className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                        Preview
                                    </span>
                                </div>
                            </div>

                            <div className="bg-slate-100 p-4">
                                <div className="rounded-[24px] bg-slate-950 p-3 shadow-xl">
                                    <div className="mb-3 flex items-center justify-between px-1 text-[10px] font-medium text-white/70">
                                        <span>
                                            9:41
                                        </span>

                                        <span>
                                            now
                                        </span>
                                    </div>

                                    <div className="rounded-2xl bg-white p-4 shadow-sm">
                                        <div className="flex items-start gap-3">
                                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-white">
                                                <BellRing
                                                    size={
                                                        18
                                                    }
                                                />
                                            </div>

                                            <div className="min-w-0 flex-1">
                                                <div className="flex items-center justify-between gap-2">
                                                    <p className="truncate text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                        Burmese Shave Club
                                                    </p>

                                                    <span className="shrink-0 text-[10px] text-slate-400">
                                                        now
                                                    </span>
                                                </div>

                                                <p className="mt-1.5 text-sm font-semibold leading-5 text-slate-950">
                                                    {values.title.trim() !==
                                                    ""
                                                        ? values.title
                                                        : "Notification title"}
                                                </p>

                                                <p className="mt-1 text-xs leading-5 text-slate-600">
                                                    {values.body.trim() !==
                                                    ""
                                                        ? values.body
                                                        : "Your message will appear here as you type."}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="mt-3 rounded-2xl border border-white/10 bg-white/5 px-3 py-2.5">
                                        <p className="text-[10px] uppercase tracking-wide text-white/50">
                                            Type
                                        </p>

                                        <p className="mt-1 text-xs font-medium text-white">
                                            {selectedTypeOption?.label ??
                                                "General"}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div className="mb-4">
                                <p className="text-sm font-semibold text-slate-900">
                                    Delivery
                                </p>

                                <p className="mt-1 text-xs leading-5 text-slate-500">
                                    This notification is sent to all active customer accounts.
                                </p>
                            </div>

                            <div className="space-y-4">
                                <div className="flex items-start gap-3">
                                    <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                                        <UsersRound
                                            size={
                                                17
                                            }
                                        />
                                    </div>

                                    <div>
                                        <p className="text-sm font-medium text-slate-800">
                                            Audience
                                        </p>

                                        <p className="mt-0.5 text-xs text-slate-500">
                                            All active customers
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-start gap-3">
                                    <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                                        <Database
                                            size={
                                                17
                                            }
                                        />
                                    </div>

                                    <div>
                                        <p className="text-sm font-medium text-slate-800">
                                            Notification center
                                        </p>

                                        <p className="mt-0.5 text-xs leading-5 text-slate-500">
                                            Saved for every active customer.
                                        </p>
                                    </div>
                                </div>

                                <div className="flex items-start gap-3">
                                    <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                                        <Smartphone
                                            size={
                                                17
                                            }
                                        />
                                    </div>

                                    <div>
                                        <p className="text-sm font-medium text-slate-800">
                                            Push notification
                                        </p>

                                        <p className="mt-0.5 text-xs leading-5 text-slate-500">
                                            Delivered to customers with an active push token.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {requiresProduct ? (
                            <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Tap action
                                </p>

                                <p className="mt-2 text-sm font-medium text-slate-800">
                                    {selectedProduct
                                        ? `Open ${selectedProduct.name}`
                                        : "Select a product"}
                                </p>

                                <p className="mt-1 text-xs leading-5 text-slate-500">
                                    Product notifications will open the selected product detail page.
                                </p>
                            </div>
                        ) : null}
                    </aside>
                </div>
            </form>
        </div>
    );
}