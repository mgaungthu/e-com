import axios from "axios";

import {
    useState,
} from "react";

import {
    useNavigate,
} from "react-router-dom";

import HomeBannerForm from "@/features/home-banners/components/HomeBannerForm";

import { useCreateHomeBanner } from "@/features/home-banners/hooks/useHomeBannerMutations";

import { useHomeBannerFormOptions } from "@/features/home-banners/hooks/useHomeBanners";

import type {
    LaravelErrorResponse,
    LaravelValidationErrors,
} from "@/features/home-banners/types/homeBanner.types";

export default function HomeBannerCreatePage() {
    const navigate =
        useNavigate();

    const createMutation =
        useCreateHomeBanner();

    const optionsQuery =
        useHomeBannerFormOptions();

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

    if (
        optionsQuery.isLoading
    ) {
        return (
            <div className="rounded-xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-500">
                Loading form...
            </div>
        );
    }

    if (
        optionsQuery.isError
    ) {
        return (
            <div className="rounded-xl border border-red-200 bg-red-50 p-6 text-sm text-red-700">
                Unable to load products.
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <div>
                <button
                    type="button"
                    onClick={() =>
                        navigate(
                            "/home-banners",
                        )
                    }
                    className="mb-3 text-sm font-medium text-blue-600 transition hover:text-blue-700"
                >
                    ← Back to Home Banners
                </button>

                <h1 className="text-2xl font-bold text-slate-900">
                    Create Home Banner
                </h1>

                <p className="mt-1 text-sm text-slate-500">
                    Upload a banner image and choose the product customers should open when they tap it.
                </p>
            </div>

            <HomeBannerForm
                products={
                    optionsQuery.data
                        ?.products ??
                    []
                }
                isSubmitting={
                    createMutation.isPending
                }
                validationErrors={
                    validationErrors
                }
                formError={
                    formError
                }
                onCancel={() =>
                    navigate(
                        "/home-banners",
                    )
                }
                onSubmit={async (
                    values,
                ) => {
                    setValidationErrors(
                        {},
                    );

                    setFormError(
                        null,
                    );

                    try {
                        await createMutation.mutateAsync(
                            values,
                        );

                        navigate(
                            "/home-banners",
                        );
                    } catch (
                        error
                    ) {
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
                                    "Unable to create home banner.",
                            );

                            return;
                        }

                        setFormError(
                            "Unable to create home banner.",
                        );
                    }
                }}
            />
        </div>
    );
}