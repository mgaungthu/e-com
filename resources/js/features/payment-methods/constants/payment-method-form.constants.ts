import type {
    PaymentMethodFormValues,
    PaymentMethodValidationErrors,
} from "@/features/payment-methods/types/paymentMethod.types";

export const EMPTY_PAYMENT_METHOD_VALIDATION_ERRORS: PaymentMethodValidationErrors =
    {};

export const PAYMENT_METHOD_FORM_INITIAL_VALUES: PaymentMethodFormValues =
    {
        name: "",
        code: "",
        type: "ewallet",

        account_name: "",
        account_number: "",
        instructions: "",

        logo: null,
        qr_image: null,

        remove_logo: false,
        remove_qr_image: false,

        requires_proof: true,
        is_active: true,

        sort_order: "0",
    };

export const PAYMENT_METHOD_VALIDATION_FIELD_ORDER: Array<
    keyof PaymentMethodFormValues
> = [
    "name",
    "code",
    "type",

    "account_name",
    "account_number",

    "logo",
    "qr_image",

    "instructions",

    "requires_proof",
    "is_active",

    "sort_order",
];