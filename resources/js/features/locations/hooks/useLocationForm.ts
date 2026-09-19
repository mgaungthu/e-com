import {
    useState,
    type FormEvent,
} from "react";

import axios from "axios";

import {
    LOCATION_FORM_INITIAL_VALUES,
    LOCATION_VALIDATION_FIELD_ORDER,
} from "@/features/locations/constants/location-form.constants";

import type {
    Location,
    LocationFormErrorResponse,
    LocationFormValues,
    LocationValidationErrors,
} from "@/features/locations/types/location.types";

import { useFocusFirstError } from "@/hooks/forms";

type UseLocationFormParams = {
    location?: Location | null;

    onSubmit: (
        values: LocationFormValues,
    ) => Promise<void>;
};

function getInitialValues(
    location?: Location | null,
): LocationFormValues {
    if (!location) {
        return {
            ...LOCATION_FORM_INITIAL_VALUES,
        };
    }

    return {
        parent_id:
            location.parent_id !== null
                ? String(
                      location.parent_id,
                  )
                : "",

        name_en:
            location.name_en,

        name_mm:
            location.name_mm ?? "",

        type:
            location.type,

        shipping_fee:
            location.shipping_fee !==
            null
                ? String(
                      location.shipping_fee,
                  )
                : "",

        is_active:
            location.is_active,

        sort_order:
            location.sort_order,
    };
}

export function useLocationForm({
    location,
    onSubmit,
}: UseLocationFormParams) {
    const [
        values,
        setValues,
    ] =
        useState<LocationFormValues>(
            () =>
                getInitialValues(
                    location,
                ),
        );

    const [
        formError,
        setFormError,
    ] =
        useState<string | null>(
            null,
        );

    const [
        errors,
        setErrors,
    ] =
        useState<LocationValidationErrors>(
            {},
        );

    const [
        focusErrors,
        setFocusErrors,
    ] =
        useState<LocationValidationErrors>(
            {},
        );

    useFocusFirstError({
        errors: focusErrors,

        fieldOrder:
            LOCATION_VALIDATION_FIELD_ORDER,
    });

    function getFieldError(
        field: keyof LocationFormValues,
    ): string | null {
        return (
            errors[field]?.[0] ??
            null
        );
    }

    function clearFieldError(
        field: keyof LocationFormValues,
    ) {
        setErrors((current) => {
            if (!current[field]) {
                return current;
            }

            const next = {
                ...current,
            };

            delete next[field];

            return next;
        });
    }

    function updateValue<
        Key extends keyof LocationFormValues,
    >(
        key: Key,
        value: LocationFormValues[Key],
    ) {
        setValues((current) => ({
            ...current,

            [key]: value,
        }));

        clearFieldError(key);

        setFormError(null);
    }

    function buildSubmittedValues(): LocationFormValues {
        return {
            ...values,

            parent_id:
                values.parent_id.trim(),

            name_en:
                values.name_en.trim(),

            name_mm:
                values.name_mm.trim(),

            type:
                values.type.trim(),

            shipping_fee:
                values.shipping_fee.trim(),
        };
    }

    function handleAxiosError(
        error: unknown,
    ) {
        if (
            !axios.isAxiosError<LocationFormErrorResponse>(
                error,
            )
        ) {
            setFormError(
                "An unexpected error occurred.",
            );

            return;
        }

        if (!error.response) {
            setFormError(
                "Unable to connect to the server.",
            );

            return;
        }

        const responseData =
            error.response.data;

        if (
            error.response.status ===
            422
        ) {
            const nextErrors =
                responseData.errors ??
                {};

            setErrors(
                nextErrors,
            );

            setFocusErrors(
                nextErrors,
            );

            setFormError(
                responseData.message ??
                    "Please check the form information.",
            );

            return;
        }

        if (
            error.response.status ===
            403
        ) {
            setFormError(
                responseData.message ??
                    "You do not have permission to perform this action.",
            );

            return;
        }

        setFormError(
            responseData.message ??
                "Unable to save the location.",
        );
    }

    async function handleSubmit(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        setFormError(null);

        setErrors({});

        setFocusErrors({});

        try {
            await onSubmit(
                buildSubmittedValues(),
            );
        } catch (error) {
            handleAxiosError(
                error,
            );
        }
    }

    return {
        values,

        errors,

        formError,

        getFieldError,

        updateValue,

        handleSubmit,
    };
}