import { type FormEvent, useEffect, useMemo, useState } from "react";

import { Package } from "lucide-react";

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
import {
    EMPTY_PRODUCT_VALIDATION_ERRORS,
    PRODUCT_FORM_INITIAL_VALUES,
    PRODUCT_VALIDATION_FIELD_ORDER,
} from "@/features/products/constants/product-form.constants";
import type {
    ProductFormProps,
    ProductFormValues,
    ProductValidationErrors,
} from "@/features/products/types/product.types";
import { useFocusFirstError, useImagePreview } from "@/hooks/forms";

export function ProductForm({
    product,
    categories,
    isSubmitting,
    validationErrors = EMPTY_PRODUCT_VALIDATION_ERRORS,
    formError = null,
    onSubmit,
}: ProductFormProps) {
    const [values, setValues] = useState<ProductFormValues>(
        PRODUCT_FORM_INITIAL_VALUES,
    );
    const [fieldErrors, setFieldErrors] = useState<ProductValidationErrors>({});

    const { previewUrl, setFilePreview, removePreview } = useImagePreview(
        product?.image_url ?? null,
    );

    useFocusFirstError({
        errors: validationErrors,
        fieldOrder: PRODUCT_VALIDATION_FIELD_ORDER,
    });

    useEffect(() => {
        setFieldErrors(validationErrors);
    }, [validationErrors]);

    useEffect(() => {
        if (!product) {
            setValues({ ...PRODUCT_FORM_INITIAL_VALUES });
            setFieldErrors({});
            return;
        }

        setValues({
            category_id: product.category_id?.toString() ?? "",
            name: product.name,
            slug: product.slug,
            sku: product.sku,
            barcode: product.barcode ?? "",
            short_description: product.short_description ?? "",
            description: product.description ?? "",
            price: product.price,
            sale_price: product.sale_price ?? "",
            stock_quantity: product.stock_quantity.toString(),
            low_stock_threshold: product.low_stock_threshold.toString(),
            image: null,
            remove_image: false,
            is_active: product.is_active,
            is_featured: product.is_featured,
            seo_title: product.seo_title ?? "",
            seo_description: product.seo_description ?? "",
        });
        setFieldErrors({});
    }, [product]);

    const submitLabel = useMemo(
        () => (product ? "Update product" : "Create product"),
        [product],
    );

    function getFieldError(field: keyof ProductFormValues): string | null {
        return fieldErrors[field]?.[0] ?? null;
    }

    function clearFieldError(field: keyof ProductFormValues) {
        setFieldErrors((current) => {
            if (!current[field]) {
                return current;
            }

            const nextErrors = { ...current };
            delete nextErrors[field];
            return nextErrors;
        });
    }

    function updateField<Key extends keyof ProductFormValues>(
        key: Key,
        value: ProductFormValues[Key],
    ) {
        setValues((current) => ({
            ...current,
            [key]: value,
        }));
        clearFieldError(key);

        if (key === "price") {
            clearFieldError("sale_price");
        }
    }

    function handleImageChange(file: File | null) {
        updateField("image", file);
        updateField("remove_image", false);
        setFilePreview(file);
    }

    function handleRemoveImage() {
        updateField("image", null);
        updateField("remove_image", true);
        removePreview();
    }

    async function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        await onSubmit(values);
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-6" noValidate>
            <FormErrorAlert message={formError} />

            <FormSection
                title="Basic information"
                description="Enter the main product details and category."
                icon={<Package size={20} />}
                contentClassName="grid gap-5 p-5 md:grid-cols-2"
            >
                <FormField
                    label="Product name"
                    htmlFor="product-name"
                    required
                    error={getFieldError("name")}
                >
                    <TextInput
                        id="product-name"
                        data-form-field="name"
                        value={values.name}
                        onChange={(event) =>
                            updateField("name", event.target.value)
                        }
                        placeholder="Enter product name"
                        error={getFieldError("name")}
                        aria-describedby={
                            getFieldError("name")
                                ? "product-name-error"
                                : undefined
                        }
                    />
                </FormField>

                <FormField
                    label="Category"
                    htmlFor="product-category"
                    error={getFieldError("category_id")}
                >
                    <SelectInput
                        id="product-category"
                        data-form-field="category_id"
                        value={values.category_id}
                        onChange={(event) =>
                            updateField("category_id", event.target.value)
                        }
                        error={getFieldError("category_id")}
                        aria-describedby={
                            getFieldError("category_id")
                                ? "product-category-error"
                                : undefined
                        }
                    >
                        <option value="">No category</option>
                        {categories.map((category) => (
                            <option key={category.id} value={category.id}>
                                {category.name}
                            </option>
                        ))}
                    </SelectInput>
                </FormField>

                <FormField
                    label="SKU"
                    htmlFor="product-sku"
                    required
                    helperText="Use a unique stock keeping unit."
                    error={getFieldError("sku")}
                >
                    <TextInput
                        id="product-sku"
                        data-form-field="sku"
                        value={values.sku}
                        onChange={(event) =>
                            updateField("sku", event.target.value)
                        }
                        placeholder="e.g. PROD-001"
                        error={getFieldError("sku")}
                        aria-describedby={
                            getFieldError("sku")
                                ? "product-sku-error"
                                : undefined
                        }
                    />
                </FormField>

                <FormField
                    label="Barcode"
                    htmlFor="product-barcode"
                    error={getFieldError("barcode")}
                >
                    <TextInput
                        id="product-barcode"
                        data-form-field="barcode"
                        value={values.barcode}
                        onChange={(event) =>
                            updateField("barcode", event.target.value)
                        }
                        placeholder="Enter barcode"
                        error={getFieldError("barcode")}
                        aria-describedby={
                            getFieldError("barcode")
                                ? "product-barcode-error"
                                : undefined
                        }
                    />
                </FormField>
            </FormSection>

            <FormSection
                title="Pricing and inventory"
                description="Configure product pricing and stock levels."
                contentClassName="grid gap-5 p-5 md:grid-cols-2"
            >
                <FormField
                    label="Regular price"
                    htmlFor="product-price"
                    required
                    error={getFieldError("price")}
                >
                    <NumberInput
                        id="product-price"
                        data-form-field="price"
                        prefix="$"
                        min="0"
                        step="0.01"
                        value={values.price}
                        onChange={(event) =>
                            updateField("price", event.target.value)
                        }
                        placeholder="0.00"
                        error={getFieldError("price")}
                        aria-describedby={
                            getFieldError("price")
                                ? "product-price-error"
                                : undefined
                        }
                    />
                </FormField>

                <FormField
                    label="Sale price"
                    htmlFor="product-sale-price"
                    helperText="Leave empty when the product is not on sale."
                    error={getFieldError("sale_price")}
                >
                    <NumberInput
                        id="product-sale-price"
                        data-form-field="sale_price"
                        prefix="$"
                        min="0"
                        step="0.01"
                        value={values.sale_price}
                        onChange={(event) =>
                            updateField("sale_price", event.target.value)
                        }
                        placeholder="0.00"
                        error={getFieldError("sale_price")}
                        aria-describedby={
                            getFieldError("sale_price")
                                ? "product-sale-price-error"
                                : undefined
                        }
                    />
                </FormField>

                <FormField
                    label="Stock quantity"
                    htmlFor="stock-quantity"
                    required
                    error={getFieldError("stock_quantity")}
                >
                    <NumberInput
                        id="stock-quantity"
                        data-form-field="stock_quantity"
                        min="0"
                        value={values.stock_quantity}
                        onChange={(event) =>
                            updateField("stock_quantity", event.target.value)
                        }
                        error={getFieldError("stock_quantity")}
                        aria-describedby={
                            getFieldError("stock_quantity")
                                ? "stock-quantity-error"
                                : undefined
                        }
                    />
                </FormField>

                <FormField
                    label="Low stock threshold"
                    htmlFor="low-stock-threshold"
                    required
                    helperText="The product will be marked as low stock at this quantity."
                    error={getFieldError("low_stock_threshold")}
                >
                    <NumberInput
                        id="low-stock-threshold"
                        data-form-field="low_stock_threshold"
                        min="0"
                        value={values.low_stock_threshold}
                        onChange={(event) =>
                            updateField(
                                "low_stock_threshold",
                                event.target.value,
                            )
                        }
                        error={getFieldError("low_stock_threshold")}
                        aria-describedby={
                            getFieldError("low_stock_threshold")
                                ? "low-stock-threshold-error"
                                : undefined
                        }
                    />
                </FormField>
            </FormSection>

            <FormSection
                title="Product description"
                description="Add information customers will see on the product page."
                contentClassName="space-y-5 p-5"
            >
                <FormField
                    label="Short description"
                    htmlFor="short-description"
                    error={getFieldError("short_description")}
                >
                    <TextArea
                        id="short-description"
                        data-form-field="short_description"
                        rows={3}
                        value={values.short_description}
                        onChange={(event) =>
                            updateField("short_description", event.target.value)
                        }
                        placeholder="Write a short product summary..."
                        error={getFieldError("short_description")}
                        aria-describedby={
                            getFieldError("short_description")
                                ? "short-description-error"
                                : undefined
                        }
                    />
                </FormField>

                <FormField
                    label="Full description"
                    htmlFor="description"
                    error={getFieldError("description")}
                >
                    <TextArea
                        id="description"
                        data-form-field="description"
                        rows={8}
                        value={values.description}
                        onChange={(event) =>
                            updateField("description", event.target.value)
                        }
                        placeholder="Write the full product description..."
                        error={getFieldError("description")}
                        aria-describedby={
                            getFieldError("description")
                                ? "description-error"
                                : undefined
                        }
                    />
                </FormField>
            </FormSection>

            <FormSection
                title="Product image"
                description="Upload a clear image in PNG, JPG, or WebP format."
            >
                <ImageUploadField
                    id="product-image"
                    previewUrl={previewUrl}
                    file={values.image}
                    error={getFieldError("image")}
                    emptyTitle="Click to upload an image"
                    helperText="PNG, JPG or WebP"
                    previewAlt="Product preview"
                    onChange={handleImageChange}
                    onRemove={handleRemoveImage}
                />
            </FormSection>

            <FormSection
                title="Product settings"
                description="Control product visibility and featured placement."
                contentClassName="grid gap-4 p-5 md:grid-cols-2"
            >
                <CheckboxCard
                    name="is_active"
                    data-form-field="is_active"
                    title="Active product"
                    description="Active products are visible and available in the store."
                    checked={values.is_active}
                    error={getFieldError("is_active")}
                    onChange={(checked) => updateField("is_active", checked)}
                />

                <CheckboxCard
                    name="is_featured"
                    data-form-field="is_featured"
                    title="Featured product"
                    description="Featured products can be highlighted on the storefront."
                    checked={values.is_featured}
                    error={getFieldError("is_featured")}
                    onChange={(checked) => updateField("is_featured", checked)}
                />
            </FormSection>

            <FormActions
                submitLabel={submitLabel}
                isSubmitting={isSubmitting}
            />
        </form>
    );
}
