import {
    type FormEvent,
    type ReactNode,
    useEffect,
    useState,
} from "react";

import {
    Image as ImageIcon,
} from "lucide-react";

import { HOME_BANNER_FORM_INITIAL_VALUES } from "@/features/home-banners/constants/home-banner-form.constants";

import type {
    HomeBanner,
    HomeBannerFormValues,
    HomeBannerProductOption,
    LaravelValidationErrors,
} from "@/features/home-banners/types/homeBanner.types";

type HomeBannerFormProps = {
    banner?: HomeBanner | null;

    products: HomeBannerProductOption[];

    isSubmitting: boolean;

    validationErrors?: LaravelValidationErrors;

    formError?: string | null;

    onSubmit: (
        values: HomeBannerFormValues,
    ) => Promise<void> | void;

    onCancel: () => void;
};

function toDateTimeLocal(
    value: string | null,
): string {
    if (!value) {
        return "";
    }

    const date =
        new Date(value);

    if (
        Number.isNaN(
            date.getTime(),
        )
    ) {
        return "";
    }

    const offset =
        date.getTimezoneOffset() *
        60_000;

    return new Date(
        date.getTime() - offset,
    )
        .toISOString()
        .slice(0, 16);
}

export default function HomeBannerForm({
    banner = null,

    products,

    isSubmitting,

    validationErrors = {},

    formError = null,

    onSubmit,
    onCancel,
}: HomeBannerFormProps) {
    const [
        values,
        setValues,
    ] =
        useState<HomeBannerFormValues>({
            ...HOME_BANNER_FORM_INITIAL_VALUES,
        });

    const [
        previewUrl,
        setPreviewUrl,
    ] =
        useState<string | null>(
            null,
        );

    /*
    |--------------------------------------------------------------------------
    | Populate Edit Form
    |--------------------------------------------------------------------------
    */

    useEffect(() => {
        if (!banner) {
            setValues({
                ...HOME_BANNER_FORM_INITIAL_VALUES,
            });

            return;
        }

        setValues({
            image: null,

            product_id:
                String(
                    banner.product_id,
                ),

            sort_order:
                String(
                    banner.sort_order,
                ),

            is_active:
                banner.is_active,

            starts_at:
                toDateTimeLocal(
                    banner.starts_at,
                ),

            ends_at:
                toDateTimeLocal(
                    banner.ends_at,
                ),
        });
    }, [banner]);

    /*
    |--------------------------------------------------------------------------
    | Image Preview
    |--------------------------------------------------------------------------
    */

    useEffect(() => {
        if (!values.image) {
            setPreviewUrl(
                banner?.image_url ??
                    null,
            );

            return;
        }

        const objectUrl =
            URL.createObjectURL(
                values.image,
            );

        setPreviewUrl(
            objectUrl,
        );

        return () => {
            URL.revokeObjectURL(
                objectUrl,
            );
        };
    }, [
        banner?.image_url,
        values.image,
    ]);

    function updateField<
        Key extends keyof HomeBannerFormValues,
    >(
        key: Key,
        value: HomeBannerFormValues[Key],
    ): void {
        setValues(
            (current) => ({
                ...current,

                [key]:
                    value,
            }),
        );
    }

    function errorFor(
        field: string,
    ): string | null {
        return (
            validationErrors[
                field
            ]?.[0] ?? null
        );
    }

    function inputClass(
        hasError = false,
    ): string {
        return [
            "w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition",

            hasError
                ? "border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-100"
                : "border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100",
        ].join(" ");
    }

    async function handleSubmit(
        event: FormEvent<HTMLFormElement>,
    ): Promise<void> {
        event.preventDefault();

        await onSubmit(
            values,
        );
    }

    return (
        <form
            onSubmit={
                handleSubmit
            }
            className="space-y-6"
        >
            {/* Form Error */}

            {formError ? (
                <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {formError}
                </div>
            ) : null}

            {/* Banner Image */}

            <section className="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <div className="border-b border-slate-200 px-5 py-4">
                    <h2 className="font-semibold text-slate-900">
                        Banner Image
                    </h2>

                    <p className="mt-1 text-sm text-slate-500">
                        Upload the complete banner design shown in the mobile home carousel.
                    </p>
                </div>

                <div className="space-y-5 p-5">
                    {/* Preview */}

                    <div>
                        <div className="mb-2 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <p className="text-sm font-medium text-slate-700">
                                Preview
                            </p>

                            <p className="text-xs text-slate-400">
                                Recommended size: 1200 × 660 px
                            </p>
                        </div>

                        <div className="overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                            <div className="aspect-[20/11] w-full">
                                {previewUrl ? (
                                    <img
                                        src={
                                            previewUrl
                                        }
                                        alt="Banner preview"
                                        className="h-full w-full object-cover"
                                    />
                                ) : (
                                    <div className="flex h-full flex-col items-center justify-center gap-3 px-6 text-center">
                                        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm">
                                            <ImageIcon
                                                size={
                                                    24
                                                }
                                            />
                                        </div>

                                        <div>
                                            <p className="text-sm font-medium text-slate-600">
                                                No banner image selected
                                            </p>

                                            <p className="mt-1 text-xs text-slate-400">
                                                Your banner preview will appear here.
                                            </p>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Browse / Upload */}

                    <Field
                        label={
                            banner
                                ? "Replace Banner Image"
                                : "Choose Banner Image"
                        }
                        required={
                            !banner
                        }
                        error={errorFor(
                            "image",
                        )}
                    >
                        <div
                            className={[
                                "rounded-xl border border-dashed p-4 transition",

                                errorFor(
                                    "image",
                                )
                                    ? "border-red-300 bg-red-50/50"
                                    : "border-slate-300 bg-slate-50 hover:border-blue-400 hover:bg-blue-50/30",
                            ].join(
                                " ",
                            )}
                        >
                            <input
                                type="file"
                                accept="image/png,image/jpeg,image/webp"
                                disabled={
                                    isSubmitting
                                }
                                onChange={(
                                    event,
                                ) =>
                                    updateField(
                                        "image",
                                        event.target
                                            .files?.[0] ??
                                            null,
                                    )
                                }
                                className="block w-full cursor-pointer text-sm text-slate-600 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-blue-600 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-white file:transition hover:file:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            />

                            <div className="mt-3 flex flex-col gap-1 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                                <span>
                                    JPG, PNG or WebP · Maximum 5 MB
                                </span>

                                {values.image ? (
                                    <span className="truncate font-medium text-slate-700 sm:max-w-[320px]">
                                        {
                                            values
                                                .image
                                                .name
                                        }
                                    </span>
                                ) : null}
                            </div>

                            {banner &&
                            !values.image ? (
                                <p className="mt-2 text-xs text-slate-400">
                                    No new image selected. The current banner image will remain unchanged.
                                </p>
                            ) : null}
                        </div>
                    </Field>
                </div>
            </section>

            {/* Product */}

            <section className="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <div className="border-b border-slate-200 px-5 py-4">
                    <h2 className="font-semibold text-slate-900">
                        Linked Product
                    </h2>

                    <p className="mt-1 text-sm text-slate-500">
                        Customers will open this product when they tap the banner.
                    </p>
                </div>

                <div className="p-5">
                    <Field
                        label="Product"
                        required
                        error={errorFor(
                            "product_id",
                        )}
                    >
                        <select
                            value={
                                values.product_id
                            }
                            disabled={
                                isSubmitting
                            }
                            onChange={(
                                event,
                            ) =>
                                updateField(
                                    "product_id",
                                    event.target
                                        .value,
                                )
                            }
                            className={inputClass(
                                Boolean(
                                    errorFor(
                                        "product_id",
                                    ),
                                ),
                            )}
                        >
                            <option value="">
                                Select product
                            </option>

                            {products.map(
                                (
                                    product,
                                ) => (
                                    <option
                                        key={
                                            product.id
                                        }
                                        value={
                                            product.id
                                        }
                                    >
                                        {
                                            product.name
                                        }
                                    </option>
                                ),
                            )}
                        </select>
                    </Field>
                </div>
            </section>

            {/* Display & Scheduling */}

            <section className="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <div className="border-b border-slate-200 px-5 py-4">
                    <h2 className="font-semibold text-slate-900">
                        Display & Scheduling
                    </h2>

                    <p className="mt-1 text-sm text-slate-500">
                        Control banner order, visibility and optional publishing dates.
                    </p>
                </div>

                <div className="grid gap-5 p-5 md:grid-cols-3">
                    {/* Sort Order */}

                    <Field
                        label="Sort Order"
                        error={errorFor(
                            "sort_order",
                        )}
                    >
                        <input
                            type="number"
                            min="0"
                            step="1"
                            value={
                                values.sort_order
                            }
                            disabled={
                                isSubmitting
                            }
                            onChange={(
                                event,
                            ) =>
                                updateField(
                                    "sort_order",
                                    event.target
                                        .value,
                                )
                            }
                            className={inputClass(
                                Boolean(
                                    errorFor(
                                        "sort_order",
                                    ),
                                ),
                            )}
                        />

                        <p className="mt-1 text-xs text-slate-400">
                            Lower numbers appear first.
                        </p>
                    </Field>

                    {/* Starts At */}

                    <Field
                        label="Starts At"
                        error={errorFor(
                            "starts_at",
                        )}
                    >
                        <input
                            type="datetime-local"
                            value={
                                values.starts_at
                            }
                            disabled={
                                isSubmitting
                            }
                            onChange={(
                                event,
                            ) =>
                                updateField(
                                    "starts_at",
                                    event.target
                                        .value,
                                )
                            }
                            className={inputClass(
                                Boolean(
                                    errorFor(
                                        "starts_at",
                                    ),
                                ),
                            )}
                        />

                        <p className="mt-1 text-xs text-slate-400">
                            Optional
                        </p>
                    </Field>

                    {/* Ends At */}

                    <Field
                        label="Ends At"
                        error={errorFor(
                            "ends_at",
                        )}
                    >
                        <input
                            type="datetime-local"
                            value={
                                values.ends_at
                            }
                            disabled={
                                isSubmitting
                            }
                            onChange={(
                                event,
                            ) =>
                                updateField(
                                    "ends_at",
                                    event.target
                                        .value,
                                )
                            }
                            className={inputClass(
                                Boolean(
                                    errorFor(
                                        "ends_at",
                                    ),
                                ),
                            )}
                        />

                        <p className="mt-1 text-xs text-slate-400">
                            Optional
                        </p>
                    </Field>

                    {/* Active */}

                    <div className="md:col-span-3">
                        <label
                            className={[
                                "flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition",

                                values.is_active
                                    ? "border-emerald-200 bg-emerald-50/50"
                                    : "border-slate-200 bg-white",
                            ].join(
                                " ",
                            )}
                        >
                            <input
                                type="checkbox"
                                checked={
                                    values.is_active
                                }
                                disabled={
                                    isSubmitting
                                }
                                onChange={(
                                    event,
                                ) =>
                                    updateField(
                                        "is_active",
                                        event.target
                                            .checked,
                                    )
                                }
                                className="mt-0.5 h-4 w-4 rounded border-slate-300"
                            />

                            <div>
                                <p className="text-sm font-medium text-slate-900">
                                    Active Banner
                                </p>

                                <p className="mt-1 text-xs text-slate-500">
                                    Active banners are shown in the mobile carousel when their publishing schedule allows it.
                                </p>
                            </div>
                        </label>

                        {errorFor(
                            "is_active",
                        ) ? (
                            <p className="mt-1 text-xs text-red-600">
                                {errorFor(
                                    "is_active",
                                )}
                            </p>
                        ) : null}
                    </div>
                </div>
            </section>

            {/* Actions */}

            <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    disabled={
                        isSubmitting
                    }
                    onClick={
                        onCancel
                    }
                    className="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    disabled={
                        isSubmitting
                    }
                    className="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {isSubmitting
                        ? "Saving..."
                        : banner
                          ? "Update Banner"
                          : "Create Banner"}
                </button>
            </div>
        </form>
    );
}

type FieldProps = {
    label: string;

    required?: boolean;

    error?: string | null;

    children: ReactNode;
};

function Field({
    label,
    required = false,
    error = null,
    children,
}: FieldProps) {
    return (
        <label className="block">
            <span className="mb-1.5 block text-sm font-medium text-slate-700">
                {label}

                {required ? (
                    <span className="ml-1 text-red-500">
                        *
                    </span>
                ) : null}
            </span>

            {children}

            {error ? (
                <span className="mt-1 block text-xs text-red-600">
                    {error}
                </span>
            ) : null}
        </label>
    );
}