import { useState } from "react";
import { useNavigate } from "react-router-dom";
import axios from "axios";

import PageHeader from "@/components/PageHeader";
import { useCategories } from "@/features/categories/hooks/useCategories";
import { ProductForm } from "@/features/products/components/ProductForm";
import { useCreateProduct } from "@/features/products/hooks/useProductMutations";

type ValidationErrors = Record<string, string[]>;

type ValidationErrorResponse = {
    success: false;
    message: string;
    errors?: ValidationErrors;
};

export default function ProductCreatePage() {
    const navigate = useNavigate();

    const [validationErrors, setValidationErrors] = useState<ValidationErrors>(
        {},
    );

    const [formError, setFormError] = useState<string | null>(null);

    const categoriesQuery = useCategories({
        page: 1,
        status: "active",
    });

    const createMutation = useCreateProduct();

    const categories = categoriesQuery.data?.data.data ?? [];

    return (
        <div className="space-y-6">
            <PageHeader
                title="Create Product"
                description="Add a new product to the catalog."
                backLabel="Back to products"
                onBack={() => navigate("/products")}
            />

            {formError && (
                <div className="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {formError}
                </div>
            )}

            <ProductForm
                categories={categories}
                validationErrors={validationErrors}
                isSubmitting={createMutation.isPending}
                onSubmit={async (values) => {
                    setValidationErrors({});
                    setFormError(null);

                    try {
                        await createMutation.mutateAsync(values);

                        navigate("/products");
                    } catch (error) {
                        if (
                            axios.isAxiosError<ValidationErrorResponse>(error)
                        ) {
                            const responseData = error.response?.data;

                            if (
                                error.response?.status === 422 &&
                                responseData?.errors
                            ) {
                                setValidationErrors(responseData.errors);

                                setFormError(
                                    responseData.message ??
                                        "Please check the form fields.",
                                );

                                return;
                            }

                            setFormError(
                                responseData?.message ??
                                    "Unable to create product.",
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
