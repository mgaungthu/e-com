import { api } from "@/api/client";

import type {
    PaymentMethodFilters,
    PaymentMethodFormValues,
    PaymentMethodListResponse,
    PaymentMethodResponse,
} from "@/features/payment-methods/types/paymentMethod.types";

type PaymentMethodFormDataValue =
    | string
    | number
    | boolean
    | File
    | null
    | undefined;

function appendValue(
    formData: FormData,
    key: string,
    value: PaymentMethodFormDataValue,
    options?: {
        skipEmptyString?: boolean;
    },
) {
    if (
        value === null ||
        value === undefined
    ) {
        return;
    }

    if (
        options?.skipEmptyString &&
        typeof value === "string" &&
        value.trim() === ""
    ) {
        return;
    }

    if (value instanceof File) {
        formData.append(
            key,
            value,
        );

        return;
    }

    if (
        typeof value === "boolean"
    ) {
        formData.append(
            key,
            value ? "1" : "0",
        );

        return;
    }

    formData.append(
        key,
        String(value),
    );
}

function toPaymentMethodFormData(
    values: PaymentMethodFormValues,
): FormData {
    const formData = new FormData();

    appendValue(
        formData,
        "name",
        values.name.trim(),
    );

    appendValue(
        formData,
        "code",
        values.code.trim(),
    );

    appendValue(
        formData,
        "type",
        values.type,
    );

    appendValue(
        formData,
        "account_name",
        values.account_name,
        {
            skipEmptyString: true,
        },
    );

    appendValue(
        formData,
        "account_number",
        values.account_number,
        {
            skipEmptyString: true,
        },
    );

    appendValue(
        formData,
        "instructions",
        values.instructions,
        {
            skipEmptyString: true,
        },
    );

    appendValue(
        formData,
        "logo",
        values.logo,
    );

    appendValue(
        formData,
        "qr_image",
        values.qr_image,
    );

    appendValue(
        formData,
        "remove_logo",
        values.remove_logo,
    );

    appendValue(
        formData,
        "remove_qr_image",
        values.remove_qr_image,
    );

    appendValue(
        formData,
        "requires_proof",
        values.requires_proof,
    );

    appendValue(
        formData,
        "is_active",
        values.is_active,
    );

    appendValue(
        formData,
        "sort_order",
        values.sort_order,
    );

    return formData;
}

export const paymentMethodApi = {
    async list(
        filters: PaymentMethodFilters,
    ): Promise<PaymentMethodListResponse> {
        const response =
            await api.get<PaymentMethodListResponse>(
                "/admin/payment-methods",
                {
                    params: filters,
                },
            );

        return response.data;
    },

    async show(
        paymentMethodId: number,
    ): Promise<PaymentMethodResponse> {
        const response =
            await api.get<PaymentMethodResponse>(
                `/admin/payment-methods/${paymentMethodId}`,
            );

        return response.data;
    },

    async create(
        values: PaymentMethodFormValues,
    ): Promise<PaymentMethodResponse> {
        const formData =
            toPaymentMethodFormData(
                values,
            );

        const response =
            await api.post<PaymentMethodResponse>(
                "/admin/payment-methods",
                formData,
                {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With":
                            "XMLHttpRequest",
                    },
                },
            );

        return response.data;
    },

    async update(
        paymentMethodId: number,
        values: PaymentMethodFormValues,
    ): Promise<PaymentMethodResponse> {
        const formData =
            toPaymentMethodFormData(
                values,
            );

        const response =
            await api.post<PaymentMethodResponse>(
                `/admin/payment-methods/${paymentMethodId}`,
                formData,
                {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With":
                            "XMLHttpRequest",
                    },
                },
            );

        return response.data;
    },

    async remove(
        paymentMethodId: number,
    ): Promise<void> {
        await api.delete(
            `/admin/payment-methods/${paymentMethodId}`,
            {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With":
                        "XMLHttpRequest",
                },
            },
        );
    },
};