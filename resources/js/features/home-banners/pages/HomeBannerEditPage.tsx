import axios from "axios";

import {
    useState,
} from "react";

import {
    Navigate,
    useNavigate,
    useParams,
} from "react-router-dom";

import HomeBannerForm from "@/features/home-banners/components/HomeBannerForm";

import { useHomeBanner } from "@/features/home-banners/hooks/useHomeBanner";

import { useUpdateHomeBanner } from "@/features/home-banners/hooks/useHomeBannerMutations";

import { useHomeBannerFormOptions } from "@/features/home-banners/hooks/useHomeBanners";

import type {
    LaravelErrorResponse,
    LaravelValidationErrors,
} from "@/features/home-banners/types/homeBanner.types";

export default function HomeBannerEditPage() {
    const navigate =
        useNavigate();

    const params =
        useParams<{
            bannerId: string;
        }>();

    const bannerId =
        Number(
            params.bannerId,
        );

    const validBannerId =
        Number.isInteger(
            bannerId,
        ) &&
        bannerId > 0;

    const bannerQuery =
        useHomeBanner(
            validBannerId
                ? bannerId
                : null,
        );

    const optionsQuery =
        useHomeBannerFormOptions();

    const updateMutation =
        useUpdateHomeBanner(
            bannerId,
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

    if (!validBannerId) {
        return (
            <Navigate
                to="/home-banners"
                replace
            />
        );
    }

    if (
        bannerQuery.isLoading ||
        optionsQuery.isLoading
    ) {
        return (
            <div className="rounded-xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-500">
                Loading banner...
            </div>
        );
    }

    if (
        bannerQuery.isError ||
        !bannerQuery.data
            ?.data.banner
    ) {
        return (
            <div className="rounded-xl border border-red-200 bg-red-50 p-6">
                <p className="text-sm text-red-700">
                    Unable to load this home banner.
                </p>

                <button
                    type="button"
                    onClick={() =>
                        navigate(
                            "/home-banners",
                        )
                    }
                    className="mt-4 text-sm font-semibold text-blue-600"
                >
                    Back to Home Banners
                </button>
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

    const banner =
        bannerQuery.data
            .data.banner;

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
                    Edit Home Banner
                </h1>

                <p className="mt-1 text-sm text-slate-500">
                    Update the banner image, linked product, order or publishing schedule.
                </p>
            </div>

            <HomeBannerForm
                banner={
                    banner
                }
                products={
                    optionsQuery.data
                        ?.products ??
                    []
                }
                isSubmitting={
                    updateMutation.isPending
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
                        await updateMutation.mutateAsync(
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
                                    "Unable to update home banner.",
                            );

                            return;
                        }

                        setFormError(
                            "Unable to update home banner.",
                        );
                    }
                }}
            />
        </div>
    );
}