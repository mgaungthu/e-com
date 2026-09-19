import {
    useState,
} from "react";

import axios from "axios";
import { useNavigate } from "react-router-dom";

import PageHeader from "@/components/PageHeader";

import { PaymentMethodForm } from "@/features/payment-methods/components/PaymentMethodForm";

import { useCreatePaymentMethod } from "@/features/payment-methods/hooks/usePaymentMethodMutations";

import type {
    PaymentMethodFormValues,
    PaymentMethodValidationErrors,
} from "@/features/payment-methods/types/paymentMethod.types";

type ValidationErrorResponse = {
    success: false;
    message: string;

    errors?: PaymentMethodValidationErrors;
};

export default function PaymentMethodCreatePage() {
    const navigate =
        useNavigate();

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

    const createMutation =
        useCreatePaymentMethod();

    async function handleSubmit(
        values: PaymentMethodFormValues,
    ) {
        setValidationErrors({});
        setFormError(null);

        try {
            await createMutation.mutateAsync(
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
                        "Unable to create payment method.",
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
                title="Create Payment Method"
                description="Add a payment option customers can use during checkout."
                backLabel="Back to payment methods"
                onBack={() =>
                    navigate(
                        "/payment-methods",
                    )
                }
            />

            <PaymentMethodForm
                validationErrors={
                    validationErrors
                }
                formError={
                    formError
                }
                isSubmitting={
                    createMutation.isPending
                }
                onSubmit={
                    handleSubmit
                }
            />
        </div>
    );
}