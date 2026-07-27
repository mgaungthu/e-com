import { useNavigate, useParams } from "react-router-dom";

import FullPageLoader from "@/components/FullPageLoader";
import PageHeader from "@/components/PageHeader";
import CategoryForm from "@/features/categories/components/CategoryForm";
import { useCategory } from "@/features/categories/hooks/useCategory";
import { useUpdateCategory } from "@/features/categories/hooks/useCategoryMutations";

export default function CategoryEditPage() {
    const navigate = useNavigate();
    const params = useParams();

    const categoryId = Number(params.categoryId);

    const categoryQuery = useCategory(
        Number.isFinite(categoryId) ? categoryId : null,
    );

    const updateMutation = useUpdateCategory(categoryId);

    if (!Number.isFinite(categoryId)) {
        return (
            <div className="rounded-xl border border-red-200 bg-red-50 p-6 text-red-700">
                Invalid category ID.
            </div>
        );
    }

    if (categoryQuery.isLoading) {
        return <FullPageLoader />;
    }

    if (categoryQuery.isError || !categoryQuery.data) {
        return (
            <div className="rounded-xl border border-red-200 bg-red-50 p-6">
                <p className="text-sm text-red-700">
                    Unable to load the category.
                </p>

                <button
                    type="button"
                    onClick={() => navigate("/categories")}
                    className="mt-4 text-sm font-medium text-blue-600"
                >
                    Back to categories
                </button>
            </div>
        );
    }

    const category = categoryQuery.data.data.category;

    return (
        <div className="space-y-6">
            <PageHeader
                title="Edit category"
                description={`Update ${category.name}.`}
                backLabel="Back to categories"
                onBack={() => navigate("/categories")}
            />

            <CategoryForm
                key={category.id}
                category={category}
                isSubmitting={updateMutation.isPending}
                submitLabel="Save changes"
                onCancel={() => navigate("/categories")}
                onSubmit={async (values) => {
                    await updateMutation.mutateAsync(values);
                    navigate("/categories");
                }}
            />
        </div>
    );
}
