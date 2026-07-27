import {
    useState,
    type ChangeEvent,
    type FormEvent,
} from "react";

import axios from "axios";

import {
    CATEGORY_FORM_INITIAL_VALUES,
    CATEGORY_VALIDATION_FIELD_ORDER,
} from "@/features/categories/constants/category-form.constants";
import { generateCategorySlug } from "@/features/categories/helpers/category-form.helpers";
import type {
    Category,
    CategoryFormErrorResponse,
    CategoryFormValues,
    CategoryValidationErrors,
} from "@/features/categories/types/category.types";
import {
    useFocusFirstError,
    useImagePreview,
} from "@/hooks/forms";

type UseCategoryFormParams = {
    category?: Category | null;
    onSubmit: (
        values: CategoryFormValues,
    ) => Promise<void>;
};

function getInitialValues(
    category?: Category | null,
): CategoryFormValues {
    if (!category) {
        return {
            ...CATEGORY_FORM_INITIAL_VALUES,
        };
    }

    return {
        parent_id: category.parent_id
            ? String(category.parent_id)
            : "",
        name: category.name,
        slug: category.slug,
        description: category.description ?? "",
        image: null,
        remove_image: false,
        is_active: category.is_active,
        sort_order: category.sort_order,
        seo_title: category.seo_title ?? "",
        seo_description:
            category.seo_description ?? "",
    };
}

export function useCategoryForm({
    category,
    onSubmit,
}: UseCategoryFormParams) {
    const [values, setValues] =
        useState<CategoryFormValues>(() =>
            getInitialValues(category),
        );

    const [
        isSlugManuallyEdited,
        setIsSlugManuallyEdited,
    ] = useState(Boolean(category?.slug));

    const [formError, setFormError] =
        useState<string | null>(null);

    const [errors, setErrors] =
        useState<CategoryValidationErrors>({});

    const [focusErrors, setFocusErrors] =
        useState<CategoryValidationErrors>({});

    const {
        previewUrl,
        setFilePreview,
        removePreview,
    } = useImagePreview(
        category?.image_url ?? null,
    );

    useFocusFirstError({
        errors: focusErrors,
        fieldOrder:
            CATEGORY_VALIDATION_FIELD_ORDER,
    });

    function getFieldError(
        field: keyof CategoryFormValues,
    ): string | null {
        return errors[field]?.[0] ?? null;
    }

    function clearFieldError(
        field: keyof CategoryFormValues,
    ) {
        setErrors((current) => {
            if (!current[field]) {
                return current;
            }

            const next = { ...current };
            delete next[field];

            return next;
        });
    }

    function updateValue<
        Key extends keyof CategoryFormValues,
    >(
        key: Key,
        value: CategoryFormValues[Key],
    ) {
        setValues((current) => ({
            ...current,
            [key]: value,
        }));

        clearFieldError(key);
        setFormError(null);
    }

    function handleNameChange(
        event: ChangeEvent<HTMLInputElement>,
    ) {
        const name = event.target.value;

        setValues((current) => ({
            ...current,
            name,
            slug: isSlugManuallyEdited
                ? current.slug
                : generateCategorySlug(name),
        }));

        setErrors((current) => {
            const next = { ...current };

            delete next.name;

            if (!isSlugManuallyEdited) {
                delete next.slug;
            }

            return next;
        });

        setFormError(null);
    }

    function handleSlugChange(
        event: ChangeEvent<HTMLInputElement>,
    ) {
        const slug = generateCategorySlug(
            event.target.value,
        );

        setIsSlugManuallyEdited(slug.length > 0);
        updateValue("slug", slug);
    }

    function handleSlugBlur() {
        if (values.slug.trim()) {
            return;
        }

        setIsSlugManuallyEdited(false);

        updateValue(
            "slug",
            generateCategorySlug(values.name),
        );
    }

    function handleImageChange(
        file: File | null,
    ) {
        if (!file) {
            return;
        }

        setValues((current) => ({
            ...current,
            image: file,
            remove_image: false,
        }));

        clearFieldError("image");
        setFormError(null);
        setFilePreview(file);
    }

    function handleRemoveImage() {
        setValues((current) => ({
            ...current,
            image: null,
            remove_image: true,
        }));

        clearFieldError("image");
        setFormError(null);
        removePreview();
    }

    function buildSubmittedValues(): CategoryFormValues {
        return {
            ...values,
            name: values.name.trim(),
            slug:
                values.slug.trim() ||
                generateCategorySlug(values.name),
            description:
                values.description.trim(),
            seo_title:
                values.seo_title.trim(),
            seo_description:
                values.seo_description.trim(),
        };
    }

    function handleAxiosError(
        error: unknown,
    ) {
        if (
            !axios.isAxiosError<CategoryFormErrorResponse>(
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

        if (error.response.status === 422) {
            const nextErrors =
                responseData.errors ?? {};

            setErrors(nextErrors);
            setFocusErrors(nextErrors);

            setFormError(
                responseData.message ??
                    "Please check the form information.",
            );

            return;
        }

        if (error.response.status === 403) {
            setFormError(
                responseData.message ??
                    "You do not have permission to perform this action.",
            );

            return;
        }

        setFormError(
            responseData.message ??
                "Unable to save the category.",
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
            handleAxiosError(error);
        }
    }

    return {
        values,
        errors,
        formError,
        previewUrl,

        getFieldError,
        updateValue,

        handleNameChange,
        handleSlugChange,
        handleSlugBlur,
        handleImageChange,
        handleRemoveImage,
        handleSubmit,
    };
}