import { useMemo } from "react";

import {
    MapPin,
    Save,
} from "lucide-react";

import {
    CheckboxCard,
    FormActions,
    FormErrorAlert,
    FormField,
    FormSection,
    NumberInput,
    SelectInput,
    TextInput,
} from "@/components/forms";

import {
    getLocationTypeLabel,
    LOCATION_TYPE_OPTIONS,
} from "@/features/locations/constants/location-form.constants";

import { useLocationForm } from "@/features/locations/hooks/useLocationForm";
import { useLocations } from "@/features/locations/hooks/useLocations";

import type { LocationFormProps } from "@/features/locations/types/location.types";

export default function LocationForm({
    location,
    isSubmitting,
    submitLabel,
    onSubmit,
    onCancel,
}: LocationFormProps) {
    const {
        values,
        formError,
        getFieldError,
        updateValue,
        handleSubmit,
    } = useLocationForm({
        location,
        onSubmit,
    });

    const parentLocationsQuery =
        useLocations({
            page: 1,

            status: "all",

            perPage: 100,
        });

    const parentLocations =
        useMemo(() => {
            const items =
                parentLocationsQuery
                    .data?.data
                    .data ?? [];

            return items.filter(
                (item) =>
                    item.id !==
                    location?.id,
            );
        }, [
            location?.id,
            parentLocationsQuery.data,
        ]);

    return (
        <form
            onSubmit={handleSubmit}
            className="space-y-6"
            noValidate
        >
            <FormErrorAlert
                message={formError}
                className="rounded-lg font-normal"
            />

            <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                <div className="space-y-6">
                    {/*
                    |--------------------------------------------------------------------------
                    | Basic Information
                    |--------------------------------------------------------------------------
                    */}

                    <FormSection
                        title="Basic information"
                        headerClassName="px-6 pt-6"
                        contentClassName="grid gap-5 p-6 pt-5 md:grid-cols-2"
                    >
                        <FormField
                            label="English name"
                            htmlFor="name_en"
                            required
                            error={getFieldError(
                                "name_en",
                            )}
                        >
                            <TextInput
                                id="name_en"
                                name="name_en"
                                data-form-field="name_en"
                                value={
                                    values.name_en
                                }
                                onChange={(
                                    event,
                                ) =>
                                    updateValue(
                                        "name_en",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                disabled={
                                    isSubmitting
                                }
                                autoComplete="off"
                                placeholder="Example: South Okkalapa"
                                className="px-4 py-3"
                                error={getFieldError(
                                    "name_en",
                                )}
                                aria-describedby={
                                    getFieldError(
                                        "name_en",
                                    )
                                        ? "name_en-error"
                                        : undefined
                                }
                            />
                        </FormField>

                        <FormField
                            label="Myanmar name"
                            htmlFor="name_mm"
                            error={getFieldError(
                                "name_mm",
                            )}
                        >
                            <TextInput
                                id="name_mm"
                                name="name_mm"
                                data-form-field="name_mm"
                                value={
                                    values.name_mm
                                }
                                onChange={(
                                    event,
                                ) =>
                                    updateValue(
                                        "name_mm",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                disabled={
                                    isSubmitting
                                }
                                autoComplete="off"
                                placeholder="ဥပမာ - တောင်ဥက္ကလာပ"
                                className="px-4 py-3"
                                error={getFieldError(
                                    "name_mm",
                                )}
                                aria-describedby={
                                    getFieldError(
                                        "name_mm",
                                    )
                                        ? "name_mm-error"
                                        : undefined
                                }
                            />
                        </FormField>

                        <FormField
                            label="Location type"
                            htmlFor="type"
                            required
                            error={getFieldError(
                                "type",
                            )}
                        >
                            <SelectInput
                                id="type"
                                name="type"
                                data-form-field="type"
                                value={
                                    values.type
                                }
                                onChange={(
                                    event,
                                ) =>
                                    updateValue(
                                        "type",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                disabled={
                                    isSubmitting
                                }
                                className="px-4 py-3"
                                error={getFieldError(
                                    "type",
                                )}
                                aria-describedby={
                                    getFieldError(
                                        "type",
                                    )
                                        ? "type-error"
                                        : undefined
                                }
                            >
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
                            </SelectInput>
                        </FormField>

                        <FormField
                            label="Parent location"
                            htmlFor="parent_id"
                            helperText="Leave empty for a top-level location such as a Region or State."
                            error={getFieldError(
                                "parent_id",
                            )}
                        >
                            <SelectInput
                                id="parent_id"
                                name="parent_id"
                                data-form-field="parent_id"
                                value={
                                    values.parent_id
                                }
                                onChange={(
                                    event,
                                ) =>
                                    updateValue(
                                        "parent_id",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                disabled={
                                    isSubmitting ||
                                    parentLocationsQuery.isLoading
                                }
                                className="px-4 py-3"
                                error={getFieldError(
                                    "parent_id",
                                )}
                                aria-describedby={
                                    getFieldError(
                                        "parent_id",
                                    )
                                        ? "parent_id-error"
                                        : undefined
                                }
                            >
                                <option value="">
                                    No parent location
                                </option>

                                {parentLocations.map(
                                    (
                                        item,
                                    ) => (
                                        <option
                                            key={
                                                item.id
                                            }
                                            value={String(
                                                item.id,
                                            )}
                                        >
                                            {
                                                item.name_en
                                            }
                                            {item.name_mm
                                                ? ` — ${item.name_mm}`
                                                : ""}
                                            {" ("}
                                            {getLocationTypeLabel(
                                                item.type,
                                            )}
                                            {")"}
                                        </option>
                                    ),
                                )}
                            </SelectInput>
                        </FormField>
                    </FormSection>

                    {/*
                    |--------------------------------------------------------------------------
                    | Delivery
                    |--------------------------------------------------------------------------
                    */}

                    <FormSection
                        title="Delivery"
                        headerClassName="px-6 pt-6"
                        contentClassName="p-6 pt-5"
                    >
                        <FormField
                            label="Shipping fee"
                            htmlFor="shipping_fee"
                            helperText="Leave blank to inherit the nearest parent location's shipping fee. If no parent has a fee, the global shipping fee will be used."
                            error={getFieldError(
                                "shipping_fee",
                            )}
                        >
                            <NumberInput
                                id="shipping_fee"
                                name="shipping_fee"
                                data-form-field="shipping_fee"
                                min={0}
                                step={1}
                                value={
                                    values.shipping_fee
                                }
                                onChange={(
                                    event,
                                ) =>
                                    updateValue(
                                        "shipping_fee",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                disabled={
                                    isSubmitting
                                }
                                placeholder="Leave blank to inherit"
                                suffix="MMK"
                                className="px-4 py-3"
                                error={getFieldError(
                                    "shipping_fee",
                                )}
                                aria-describedby={
                                    getFieldError(
                                        "shipping_fee",
                                    )
                                        ? "shipping_fee-error"
                                        : undefined
                                }
                            />
                        </FormField>

                        <div className="mt-5 rounded-xl border border-blue-100 bg-blue-50/60 p-4">
                            <div className="flex gap-3">
                                <MapPin
                                    size={18}
                                    className="mt-0.5 shrink-0 text-blue-600"
                                />

                                <div>
                                    <p className="text-sm font-semibold text-slate-900">
                                        Shipping fee inheritance
                                    </p>

                                    <p className="mt-1 text-xs leading-5 text-slate-600">
                                        A location without its own shipping fee
                                        will use the nearest parent location
                                        that has a fee configured.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </FormSection>
                </div>

                <div className="space-y-6">
                    {/*
                    |--------------------------------------------------------------------------
                    | Organization
                    |--------------------------------------------------------------------------
                    */}

                    <FormSection
                        title="Organization"
                        headerClassName="px-6 pt-6"
                        contentClassName="space-y-5 p-6 pt-5"
                    >
                        <FormField
                            label="Sort order"
                            htmlFor="sort_order"
                            helperText="Lower numbers appear first."
                            error={getFieldError(
                                "sort_order",
                            )}
                        >
                            <NumberInput
                                id="sort_order"
                                name="sort_order"
                                data-form-field="sort_order"
                                min={0}
                                step={1}
                                value={
                                    values.sort_order
                                }
                                onChange={(
                                    event,
                                ) =>
                                    updateValue(
                                        "sort_order",
                                        Number(
                                            event
                                                .target
                                                .value ||
                                                0,
                                        ),
                                    )
                                }
                                disabled={
                                    isSubmitting
                                }
                                className="px-4 py-3"
                                error={getFieldError(
                                    "sort_order",
                                )}
                                aria-describedby={
                                    getFieldError(
                                        "sort_order",
                                    )
                                        ? "sort_order-error"
                                        : undefined
                                }
                            />
                        </FormField>

                        <CheckboxCard
                            name="is_active"
                            data-form-field="is_active"
                            title="Active"
                            description="Allow customers to select this location for delivery."
                            checked={
                                values.is_active
                            }
                            disabled={
                                isSubmitting
                            }
                            error={getFieldError(
                                "is_active",
                            )}
                            onChange={(
                                checked,
                            ) =>
                                updateValue(
                                    "is_active",
                                    checked,
                                )
                            }
                        />
                    </FormSection>
                </div>
            </div>

            <FormActions
                submitLabel={
                    submitLabel
                }
                isSubmitting={
                    isSubmitting
                }
                submitDisabled={
                    !values.name_en.trim() ||
                    !values.type.trim()
                }
                onCancel={onCancel}
                submitIcon={
                    <Save size={17} />
                }
                className="border-0 bg-transparent p-0"
            />
        </form>
    );
}