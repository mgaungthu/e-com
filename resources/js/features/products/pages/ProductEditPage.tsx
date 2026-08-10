import axios from "axios";
import { useState } from "react";
import { Navigate, useNavigate, useParams } from "react-router-dom";

import PageHeader from "@/components/PageHeader";
import { useCategories } from "@/features/categories/hooks/useCategories";
import { ProductForm } from "@/features/products/components/ProductForm";
import { useProduct } from "@/features/products/hooks/useProduct";
import { useUpdateProduct } from "@/features/products/hooks/useProductMutations";

export default function ProductEditPage() {
    const navigate = useNavigate();
    const params = useParams();

    const productId = Number(params.productId);
    const [validationErrors, setValidationErrors] = useState<
        Record<string, string[]>
    >({});
    const [formError, setFormError] = useState<string | null>(null);

    const productQuery = useProduct(
        Number.isFinite(productId) ? productId : null,
    );

    const categoriesQuery = useCategories({
        page: 1,
        perPage: 100,
        status: "active",
    });

    const updateMutation = useUpdateProduct(productId);

    if (!Number.isFinite(productId)) {
        return <Navigate to="/products" replace />;
    }

    if (productQuery.isLoading || categoriesQuery.isLoading) {
        return <div>Loading product...</div>;
    }

    if (
        productQuery.isError ||
        !productQuery.data?.data.product
    ) {
        return (
            <div className="text-red-600">
                Product could not be loaded.
            </div>
        );
    }

    const product = productQuery.data.data.product;
    const categories = categoriesQuery.data?.data.data ?? [];

    return (
        <div className="space-y-6">
            <PageHeader
                title="Edit Product"
                description="Update product details and stock."
                backLabel="Back to products"
                onBack={() => navigate("/products")}
            />

            <ProductForm
                product={product}
                categories={categories}
                validationErrors={validationErrors}
                formError={formError}
                isSubmitting={updateMutation.isPending}
                onSubmit={async (values) => {
                    setValidationErrors({});
                    setFormError(null);

                    try {
                        await updateMutation.mutateAsync(
                            values,
                        );

                        navigate("/products");
                    } catch (error) {
                        if (axios.isAxiosError(error)) {
                            const responseData = error.response?.data as {
                                message?: string;
                                errors?: Record<string, string[]>;
                            } | undefined;

                            if (error.response?.status === 422) {
                                setValidationErrors(responseData?.errors ?? {});
                            }

                            setFormError(
                                responseData?.message ??
                                    "Unable to update product.",
                            );
                            return;
                        }

                        setFormError("An unexpected error occurred.");
                    }
                }}
            />
        </div>
    );
}
