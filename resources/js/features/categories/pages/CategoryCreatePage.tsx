import { useNavigate } from "react-router-dom";

import PageHeader from "@/components/PageHeader";
import CategoryForm from "@/features/categories/components/CategoryForm";
import { useCreateCategory } from "@/features/categories/hooks/useCategoryMutations";

export default function CategoryCreatePage() {
    const navigate = useNavigate();
    const createMutation = useCreateCategory();

    return (
        <div className="space-y-6">
            <PageHeader
                title="Add category"
                description="Create a new product category."
                backLabel="Back to categories"
                onBack={() => navigate("/categories")}
            />

            <CategoryForm
                isSubmitting={createMutation.isPending}
                submitLabel="Create category"
                onCancel={() => navigate("/categories")}
                onSubmit={async (values) => {
                    await createMutation.mutateAsync(values);
                    navigate("/categories");
                }}
            />
        </div>
    );
}
