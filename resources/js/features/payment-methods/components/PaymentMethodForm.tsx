import {
    type FormEvent,
    useEffect,
    useMemo,
    useState,
} from "react";

import {
    CreditCard,
    Settings,
} from "lucide-react";

import {
    CheckboxCard,
    FormActions,
    FormErrorAlert,
    FormField,
    FormSection,
    NumberInput,
    SelectInput,
    TextArea,
    TextInput,
} from "@/components/forms";

import { PaymentMethodImageField } from "@/features/payment-methods/components/PaymentMethodImageField";

import {
    EMPTY_PAYMENT_METHOD_VALIDATION_ERRORS,
    PAYMENT_METHOD_FORM_INITIAL_VALUES,
    PAYMENT_METHOD_VALIDATION_FIELD_ORDER,
} from "@/features/payment-methods/constants/payment-method-form.constants";

import type {
    PaymentMethodFormProps,
    PaymentMethodFormValues,
    PaymentMethodType,
    PaymentMethodValidationErrors,
} from "@/features/payment-methods/types/paymentMethod.types";

import { useFocusFirstError } from "@/hooks/forms";

export function PaymentMethodForm({
    paymentMethod,
    isSubmitting,
    validationErrors =
        EMPTY_PAYMENT_METHOD_VALIDATION_ERRORS,
    formError = null,
    onSubmit,
}: PaymentMethodFormProps) {
    const [
        values,
        setValues,
    ] =
        useState<PaymentMethodFormValues>(
            PAYMENT_METHOD_FORM_INITIAL_VALUES,
        );

    const [
        fieldErrors,
        setFieldErrors,
    ] =
        useState<PaymentMethodValidationErrors>(
            {},
        );

    useFocusFirstError({
        errors: validationErrors,
        fieldOrder:
            PAYMENT_METHOD_VALIDATION_FIELD_ORDER,
    });

    useEffect(() => {
        setFieldErrors(
            validationErrors,
        );
    }, [validationErrors]);

    useEffect(() => {
        if (!paymentMethod) {
            setValues({
                ...PAYMENT_METHOD_FORM_INITIAL_VALUES,
            });

            setFieldErrors({});

            return;
        }

        setValues({
            name:
                paymentMethod.name,

            code:
                paymentMethod.code,

            type:
                paymentMethod.type,

            account_name:
                paymentMethod.account_name ??
                "",

            account_number:
                paymentMethod.account_number ??
                "",

            instructions:
                paymentMethod.instructions ??
                "",

            logo: null,

            qr_image: null,

            remove_logo: false,

            remove_qr_image:
                false,

            requires_proof:
                paymentMethod.requires_proof,

            is_active:
                paymentMethod.is_active,

            sort_order:
                paymentMethod.sort_order.toString(),
        });

        setFieldErrors({});
    }, [paymentMethod]);

    const submitLabel =
        useMemo(
            () =>
                paymentMethod
                    ? "Update payment method"
                    : "Create payment method",
            [paymentMethod],
        );

    function getFieldError(
        field:
            keyof PaymentMethodFormValues,
    ): string | null {
        return (
            fieldErrors[field]?.[0] ??
            null
        );
    }

    function clearFieldError(
        field:
            keyof PaymentMethodFormValues,
    ) {
        setFieldErrors(
            (current) => {
                if (
                    !current[field]
                ) {
                    return current;
                }

                const nextErrors = {
                    ...current,
                };

                delete nextErrors[
                    field
                ];

                return nextErrors;
            },
        );
    }

    function updateField<
        Key extends keyof PaymentMethodFormValues,
    >(
        key: Key,
        value: PaymentMethodFormValues[Key],
    ) {
        setValues(
            (current) => ({
                ...current,
                [key]: value,
            }),
        );

        clearFieldError(key);
    }

    function updateType(
        type: PaymentMethodType,
    ) {
        setValues(
            (current) => ({
                ...current,

                type,

                requires_proof:
                    type === "cod"
                        ? false
                        : current.requires_proof,
            }),
        );

        clearFieldError(
            "type",
        );

        if (type === "cod") {
            clearFieldError(
                "requires_proof",
            );
        }
    }

    async function handleSubmit(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        await onSubmit(values);
    }

    return (
        <form
            onSubmit={handleSubmit}
            className="space-y-6"
            noValidate
        >
            <FormErrorAlert
                message={formError}
            />

            {/*
            |--------------------------------------------------------------------------
            | Basic Information
            |--------------------------------------------------------------------------
            */}

            <FormSection
                title="Basic information"
                description="Configure the payment method name, code, type, and display order."
                icon={
                    <CreditCard
                        size={20}
                    />
                }
                contentClassName="grid gap-5 p-5 md:grid-cols-2"
            >
                <FormField
                    label="Payment method name"
                    htmlFor="payment-method-name"
                    required
                    error={getFieldError(
                        "name",
                    )}
                >
                    <TextInput
                        id="payment-method-name"
                        data-form-field="name"
                        value={
                            values.name
                        }
                        onChange={(
                            event,
                        ) =>
                            updateField(
                                "name",
                                event.target
                                    .value,
                            )
                        }
                        placeholder="e.g. KBZPay"
                        error={getFieldError(
                            "name",
                        )}
                    />
                </FormField>

                <FormField
                    label="Code"
                    htmlFor="payment-method-code"
                    required
                    helperText="Use a stable lowercase identifier such as kbzpay or cod."
                    error={getFieldError(
                        "code",
                    )}
                >
                    <TextInput
                        id="payment-method-code"
                        data-form-field="code"
                        value={
                            values.code
                        }
                        onChange={(
                            event,
                        ) =>
                            updateField(
                                "code",
                                event.target.value.toLowerCase(),
                            )
                        }
                        placeholder="e.g. kbzpay"
                        error={getFieldError(
                            "code",
                        )}
                    />
                </FormField>

                <FormField
                    label="Payment type"
                    htmlFor="payment-method-type"
                    required
                    error={getFieldError(
                        "type",
                    )}
                >
                    <SelectInput
                        id="payment-method-type"
                        data-form-field="type"
                        value={
                            values.type
                        }
                        onChange={(
                            event,
                        ) =>
                            updateType(
                                event.target
                                    .value as PaymentMethodType,
                            )
                        }
                        error={getFieldError(
                            "type",
                        )}
                    >
                        <option value="ewallet">
                            E-Wallet
                        </option>

                        <option value="cod">
                            Cash on Delivery
                        </option>
                    </SelectInput>
                </FormField>

                <FormField
                    label="Sort order"
                    htmlFor="payment-method-sort-order"
                    required
                    helperText="Lower numbers appear first."
                    error={getFieldError(
                        "sort_order",
                    )}
                >
                    <NumberInput
                        id="payment-method-sort-order"
                        data-form-field="sort_order"
                        min="0"
                        step="1"
                        value={
                            values.sort_order
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
                        placeholder="0"
                        error={getFieldError(
                            "sort_order",
                        )}
                    />
                </FormField>
            </FormSection>

            {/*
            |--------------------------------------------------------------------------
            | Payment Details
            |--------------------------------------------------------------------------
            */}

            <FormSection
                title="Payment details"
                description={
                    values.type ===
                    "cod"
                        ? "Cash on Delivery normally does not require account information."
                        : "Enter the account information customers should use when making payment."
                }
                contentClassName="grid gap-5 p-5 md:grid-cols-2"
            >
                <FormField
                    label="Account name"
                    htmlFor="payment-account-name"
                    error={getFieldError(
                        "account_name",
                    )}
                >
                    <TextInput
                        id="payment-account-name"
                        data-form-field="account_name"
                        value={
                            values.account_name
                        }
                        onChange={(
                            event,
                        ) =>
                            updateField(
                                "account_name",
                                event.target
                                    .value,
                            )
                        }
                        placeholder="Account holder name"
                        error={getFieldError(
                            "account_name",
                        )}
                    />
                </FormField>

                <FormField
                    label="Account number"
                    htmlFor="payment-account-number"
                    error={getFieldError(
                        "account_number",
                    )}
                >
                    <TextInput
                        id="payment-account-number"
                        data-form-field="account_number"
                        value={
                            values.account_number
                        }
                        onChange={(
                            event,
                        ) =>
                            updateField(
                                "account_number",
                                event.target
                                    .value,
                            )
                        }
                        placeholder="Phone or account number"
                        error={getFieldError(
                            "account_number",
                        )}
                    />
                </FormField>

                <div className="md:col-span-2">
                    <FormField
                        label="Instructions"
                        htmlFor="payment-instructions"
                        helperText="Displayed to customers during checkout."
                        error={getFieldError(
                            "instructions",
                        )}
                    >
                        <TextArea
                            id="payment-instructions"
                            data-form-field="instructions"
                            rows={5}
                            value={
                                values.instructions
                            }
                            onChange={(
                                event,
                            ) =>
                                updateField(
                                    "instructions",
                                    event.target
                                        .value,
                                )
                            }
                            placeholder="Enter payment instructions..."
                            error={getFieldError(
                                "instructions",
                            )}
                        />
                    </FormField>
                </div>
            </FormSection>

            {/*
            |--------------------------------------------------------------------------
            | Images
            |--------------------------------------------------------------------------
            */}

            <FormSection
                title="Branding and QR"
                description="Upload the payment method logo and QR image shown to customers."
                contentClassName="grid gap-5 p-5 lg:grid-cols-2"
            >
                <PaymentMethodImageField
                    id="payment-method-logo"
                    fieldName="logo"
                    label="Payment logo"
                    description="Recommended: square PNG or WEBP with a transparent or clean background."
                    currentImageUrl={
                        paymentMethod?.logo_url ??
                        null
                    }
                    file={
                        values.logo
                    }
                    removeExisting={
                        values.remove_logo
                    }
                    disabled={
                        isSubmitting
                    }
                    error={getFieldError(
                        "logo",
                    )}
                    onChange={(
                        file,
                    ) => {
                        updateField(
                            "logo",
                            file,
                        );

                        if (file) {
                            updateField(
                                "remove_logo",
                                false,
                            );
                        }
                    }}
                    onRemoveExistingChange={(
                        remove,
                    ) => {
                        updateField(
                            "remove_logo",
                            remove,
                        );

                        if (remove) {
                            updateField(
                                "logo",
                                null,
                            );
                        }
                    }}
                />

                <PaymentMethodImageField
                    id="payment-method-qr-image"
                    fieldName="qr_image"
                    label="QR image"
                    description="Optional QR code customers can scan when paying."
                    currentImageUrl={
                        paymentMethod?.qr_image_url ??
                        null
                    }
                    file={
                        values.qr_image
                    }
                    removeExisting={
                        values.remove_qr_image
                    }
                    disabled={
                        isSubmitting
                    }
                    error={getFieldError(
                        "qr_image",
                    )}
                    onChange={(
                        file,
                    ) => {
                        updateField(
                            "qr_image",
                            file,
                        );

                        if (file) {
                            updateField(
                                "remove_qr_image",
                                false,
                            );
                        }
                    }}
                    onRemoveExistingChange={(
                        remove,
                    ) => {
                        updateField(
                            "remove_qr_image",
                            remove,
                        );

                        if (remove) {
                            updateField(
                                "qr_image",
                                null,
                            );
                        }
                    }}
                />
            </FormSection>

            {/*
            |--------------------------------------------------------------------------
            | Settings
            |--------------------------------------------------------------------------
            */}

            <FormSection
                title="Payment method settings"
                description="Control availability and proof requirements."
                icon={
                    <Settings
                        size={20}
                    />
                }
                contentClassName="grid gap-3 p-4 sm:grid-cols-2"
            >
                <CheckboxCard
                    name="is_active"
                    data-form-field="is_active"
                    title="Active"
                    description="Available for customers during checkout."
                    checked={
                        values.is_active
                    }
                    error={getFieldError(
                        "is_active",
                    )}
                    onChange={(
                        checked,
                    ) =>
                        updateField(
                            "is_active",
                            checked,
                        )
                    }
                />

                <CheckboxCard
                    name="requires_proof"
                    data-form-field="requires_proof"
                    title="Requires payment proof"
                    description={
                        values.type ===
                        "cod"
                            ? "Cash on Delivery does not require a payment screenshot."
                            : "Customer must upload a payment screenshot before placing the order."
                    }
                    checked={
                        values.requires_proof
                    }
                    error={getFieldError(
                        "requires_proof",
                    )}
                    onChange={(
                        checked,
                    ) =>
                        updateField(
                            "requires_proof",
                            values.type ===
                                "cod"
                                ? false
                                : checked,
                        )
                    }
                />
            </FormSection>

            <FormActions
                submitLabel={
                    submitLabel
                }
                isSubmitting={
                    isSubmitting
                }
            />
        </form>
    );
}