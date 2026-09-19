import {
    useState,
} from "react";

import axios from "axios";

import {
    useNavigate,
    useParams,
} from "react-router-dom";

import PageHeader from "@/components/PageHeader";

import { PaymentMethodForm } from "@/features/payment-methods/components/PaymentMethodForm";

import { usePaymentMethod } from "@/features/payment-methods/hooks/usePaymentMethod";

import { useUpdatePaymentMethod } from "@/features/payment-methods/hooks/usePaymentMethodMutations";

import type {
    PaymentMethodFormValues,
    PaymentMethodValidationErrors,
} from "@/features/payment-methods/types/paymentMethod.types";

type ValidationErrorResponse = {
    success: false;
    message: string;

    errors?: PaymentMethodValidationErrors;
};

export default function PaymentMethodEditPage() {
    const navigate =
        useNavigate();

    const {
        paymentMethodId:
            paymentMethodIdParam,
    } = useParams();

    const parsedPaymentMethodId =
        Number.parseInt(
            paymentMethodIdParam ??
                "",
            10,
        );

    const paymentMethodId =
        Number.isInteger(
            parsedPaymentMethodId,
        ) &&
        parsedPaymentMethodId > 0
            ? parsedPaymentMethodId
            : null;

    const [
        validationErrors,
        setValidationErrors,
    ] =
        useState<PaymentMethodValidationErrors>(
            {},
        );

    const [
        formError,
        setFormError,
    ] =
        useState<string | null>(
            null,
        );

    const paymentMethodQuery =
        usePaymentMethod(
            paymentMethodId,
        );

    const updateMutation =
        useUpdatePaymentMethod(
            paymentMethodId ??
                0,
        );

    if (
        paymentMethodId ===
        null
    ) {
        return (
            <div className="rounded-xl border border-red-200 bg-white p-6">
                <h1 className="text-lg font-semibold text-slate-900">
                    Invalid payment
                    method
                </h1>

                <p className="mt-2 text-sm text-slate-600">
                    The payment
                    method ID is
                    invalid.
                </p>

                <button
                    type="button"
                    onClick={() =>
                        navigate(
                            "/payment-methods",
                        )
                    }
                    className="mt-4 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800"
                >
                    Back to payment
                    methods
                </button>
            </div>
        );
    }

    if (
        paymentMethodQuery.isLoading
    ) {
        return (
            <div className="rounded-xl border border-slate-200 bg-white p-6 text-sm text-slate-600">
                Loading payment
                method...
            </div>
        );
    }

    if (
        paymentMethodQuery.isError
    ) {
        return (
            <div className="rounded-xl border border-red-200 bg-white p-6">
                <h1 className="text-lg font-semibold text-slate-900">
                    Unable to load
                    payment method
                </h1>

                <p className="mt-2 text-sm text-slate-600">
                    Something went
                    wrong while
                    loading this
                    payment method.
                </p>

                <div className="mt-4 flex flex-wrap gap-2">
                    <button
                        type="button"
                        onClick={() =>
                            paymentMethodQuery.refetch()
                        }
                        className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800"
                    >
                        Try again
                    </button>

                    <button
                        type="button"
                        onClick={() =>
                            navigate(
                                "/payment-methods",
                            )
                        }
                        className="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Back
                    </button>
                </div>
            </div>
        );
    }

    const paymentMethod =
        paymentMethodQuery.data
            ?.data
            .payment_method;

    if (!paymentMethod) {
        return (
            <div className="rounded-xl border border-red-200 bg-white p-6">
                <h1 className="text-lg font-semibold text-slate-900">
                    Payment method
                    not found
                </h1>

                <button
                    type="button"
                    onClick={() =>
                        navigate(
                            "/payment-methods",
                        )
                    }
                    className="mt-4 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                >
                    Back to payment
                    methods
                </button>
            </div>
        );
    }

    async function handleSubmit(
        values: PaymentMethodFormValues,
    ) {
        setValidationErrors({});
        setFormError(null);

        try {
            await updateMutation.mutateAsync(
                values,
            );

            navigate(
                "/payment-methods",
            );
        } catch (error) {
            if (
                axios.isAxiosError<ValidationErrorResponse>(
                    error,
                )
            ) {
                const responseData =
                    error.response
                        ?.data;

                if (
                    error.response
                        ?.status ===
                        422 &&
                    responseData?.errors
                ) {
                    setValidationErrors(
                        responseData.errors,
                    );

                    setFormError(
                        responseData.message ??
                            "Please check the form fields.",
                    );

                    return;
                }

                setFormError(
                    responseData?.message ??
                        "Unable to update payment method.",
                );

                return;
            }

            setFormError(
                "An unexpected error occurred.",
            );
        }
    }

    return (
        <div className="space-y-6">
            <PageHeader
                title={`Edit ${paymentMethod.name}`}
                description="Update payment details, images, and checkout availability."
                backLabel="Back to payment methods"
                onBack={() =>
                    navigate(
                        "/payment-methods",
                    )
                }
            />

            <PaymentMethodForm
                paymentMethod={
                    paymentMethod
                }
                validationErrors={
                    validationErrors
                }
                formError={
                    formError
                }
                isSubmitting={
                    updateMutation.isPending
                }
                onSubmit={
                    handleSubmit
                }
            />
        </div>
    );
}