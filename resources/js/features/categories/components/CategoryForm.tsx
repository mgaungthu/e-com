import { useMemo } from "react";

import { Save } from "lucide-react";

import {
    CheckboxCard,
    FormActions,
    FormErrorAlert,
    FormField,
    FormSection,
    ImageUploadField,
    NumberInput,
    SelectInput,
    TextArea,
    TextInput,
} from "@/components/forms";
import { useCategories } from "@/features/categories/hooks/useCategories";
import { useCategoryForm } from "@/features/categories/hooks/useCategoryForm";
import type { CategoryFormProps } from "@/features/categories/types/category.types";

export default function CategoryForm({
    category,
    isSubmitting,
    submitLabel,
    onSubmit,
    onCancel,
}: CategoryFormProps) {
    const {
        values,
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
    } = useCategoryForm({
        category,
        onSubmit,
    });

    const parentCategoriesQuery = useCategories({
        page: 1,
        status: "active",
        perPage: 100,
    });

    const parentCategories = useMemo(() => {
        const items = parentCategoriesQuery.data?.data.data ?? [];

        return items.filter((item) => item.id !== category?.id);
    }, [category?.id, parentCategoriesQuery.data]);

    return (
        <form
            onSubmit={handleSubmit}
            className="space-y-6"
            encType="multipart/form-data"
            noValidate
        >
            <FormErrorAlert
                message={formError}
                className="rounded-lg font-normal"
            />

            <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                <div className="space-y-6">
                    <FormSection
                        title="Basic information"
                        headerClassName="px-6 pt-6"
                        contentClassName="grid gap-5 p-6 pt-5 md:grid-cols-2"
                    >
                        <FormField
                            label="Category name"
                            htmlFor="name"
                            className="md:col-span-2"
                            error={getFieldError("name")}
                        >
                            <TextInput
                                id="name"
                                name="name"
                                data-form-field="name"
                                value={values.name}
                                onChange={handleNameChange}
                                disabled={isSubmitting}
                                autoComplete="off"
                                placeholder="Example: Electronics"
                                className="px-4 py-3"
                                error={getFieldError("name")}
                                aria-describedby={
                                    getFieldError("name")
                                        ? "name-error"
                                        : undefined
                                }
                            />
                        </FormField>

                        <FormField
                            label="Slug"
                            htmlFor="slug"
                            helperText="Generated automatically from the category name. You can also edit it manually."
                            error={getFieldError("slug")}
                        >
                            <TextInput
                                id="slug"
                                name="slug"
                                data-form-field="slug"
                                value={values.slug}
                                onChange={handleSlugChange}
                                onBlur={handleSlugBlur}
                                disabled={isSubmitting}
                                autoComplete="off"
                                placeholder="electronics"
                                className="px-4 py-3"
                                error={getFieldError("slug")}
                                aria-describedby={
                                    getFieldError("slug")
                                        ? "slug-error"
                                        : undefined
                                }
                            />
                        </FormField>

                        <FormField
                            label="Parent category"
                            htmlFor="parent_id"
                            error={getFieldError("parent_id")}
                        >
                            <SelectInput
                                id="parent_id"
                                name="parent_id"
                                data-form-field="parent_id"
                                value={values.parent_id}
                                onChange={(event) =>
                                    updateValue("parent_id", event.target.value)
                                }
                                disabled={
                                    isSubmitting ||
                                    parentCategoriesQuery.isLoading
                                }
                                className="px-4 py-3"
                                error={getFieldError("parent_id")}
                                aria-describedby={
                                    getFieldError("parent_id")
                                        ? "parent_id-error"
                                        : undefined
                                }
                            >
                                <option value="">No parent category</option>

                                {parentCategories.map((item) => (
                                    <option
                                        key={item.id}
                                        value={String(item.id)}
                                    >
                                        {item.name}
                                    </option>
                                ))}
                            </SelectInput>
                        </FormField>

                        <FormField
                            label="Description"
                            htmlFor="category-description"
                            className="md:col-span-2"
                            error={getFieldError("description")}
                        >
                            <TextArea
                                id="category-description"
                                name="description"
                                data-form-field="description"
                                rows={5}
                                value={values.description}
                                onChange={(event) =>
                                    updateValue(
                                        "description",
                                        event.target.value,
                                    )
                                }
                                disabled={isSubmitting}
                                placeholder="Write a short category description..."
                                className="px-4 py-3"
                                error={getFieldError("description")}
                                aria-describedby={
                                    getFieldError("description")
                                        ? "category-description-error"
                                        : undefined
                                }
                            />
                        </FormField>
                    </FormSection>

                    <FormSection
                        title="SEO"
                        headerClassName="px-6 pt-6"
                        contentClassName="space-y-5 p-6 pt-5"
                    >
                        <FormField
                            label="SEO title"
                            htmlFor="seo_title"
                            error={getFieldError("seo_title")}
                        >
                            <TextInput
                                id="seo_title"
                                name="seo_title"
                                data-form-field="seo_title"
                                value={values.seo_title}
                                onChange={(event) =>
                                    updateValue("seo_title", event.target.value)
                                }
                                disabled={isSubmitting}
                                placeholder="SEO title"
                                className="px-4 py-3"
                                error={getFieldError("seo_title")}
                                aria-describedby={
                                    getFieldError("seo_title")
                                        ? "seo_title-error"
                                        : undefined
                                }
                            />
                        </FormField>

                        <FormField
                            label="SEO description"
                            htmlFor="seo_description"
                            error={getFieldError("seo_description")}
                        >
                            <TextArea
                                id="seo_description"
                                name="seo_description"
                                data-form-field="seo_description"
                                rows={4}
                                value={values.seo_description}
                                onChange={(event) =>
                                    updateValue(
                                        "seo_description",
                                        event.target.value,
                                    )
                                }
                                disabled={isSubmitting}
                                placeholder="SEO description"
                                className="px-4 py-3"
                                error={getFieldError("seo_description")}
                                aria-describedby={
                                    getFieldError("seo_description")
                                        ? "seo_description-error"
                                        : undefined
                                }
                            />
                        </FormField>
                    </FormSection>
                </div>

                <div className="space-y-6">
                    <FormSection
                        title="Category image"
                        headerClassName="px-6 pt-6"
                        contentClassName="p-6 pt-5"
                    >
                        <ImageUploadField
                            id="category-image"
                            data-form-field="image"
                            layout="stacked"
                            previewUrl={previewUrl}
                            file={values.image}
                            error={getFieldError("image")}
                            disabled={isSubmitting}
                            emptyTitle="Upload category image"
                            helperText="JPG, PNG or WebP up to 4 MB"
                            previewAlt="Category preview"
                            onChange={handleImageChange}
                            onRemove={handleRemoveImage}
                        />
                    </FormSection>

                    <FormSection
                        title="Organization"
                        headerClassName="px-6 pt-6"
                        contentClassName="space-y-5 p-6 pt-5"
                    >
                        <FormField
                            label="Sort order"
                            htmlFor="sort_order"
                            error={getFieldError("sort_order")}
                        >
                            <NumberInput
                                id="sort_order"
                                name="sort_order"
                                data-form-field="sort_order"
                                min={0}
                                step={1}
                                value={values.sort_order}
                                onChange={(event) =>
                                    updateValue(
                                        "sort_order",
                                        Number(event.target.value || 0),
                                    )
                                }
                                disabled={isSubmitting}
                                className="px-4 py-3"
                                error={getFieldError("sort_order")}
                                aria-describedby={
                                    getFieldError("sort_order")
                                        ? "sort_order-error"
                                        : undefined
                                }
                            />
                        </FormField>

                        <CheckboxCard
                            name="is_active"
                            data-form-field="is_active"
                            title="Active"
                            description="Show this category in the store."
                            checked={values.is_active}
                            disabled={isSubmitting}
                            error={getFieldError("is_active")}
                            onChange={(checked) =>
                                updateValue("is_active", checked)
                            }
                        />
                    </FormSection>
                </div>
            </div>

            <FormActions
                submitLabel={submitLabel}
                isSubmitting={isSubmitting}
                submitDisabled={!values.name.trim()}
                onCancel={onCancel}
                submitIcon={<Save size={17} />}
                className="border-0 bg-transparent p-0"
            />
        </form>
    );
}
