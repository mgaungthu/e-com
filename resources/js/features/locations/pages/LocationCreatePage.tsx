import { useNavigate } from "react-router-dom";

import PageHeader from "@/components/PageHeader";

import LocationForm from "@/features/locations/components/LocationForm";

import { useCreateLocation } from "@/features/locations/hooks/useLocationMutations";

export default function LocationCreatePage() {
    const navigate =
        useNavigate();

    const createMutation =
        useCreateLocation();

    return (
        <div className="space-y-6">
            <PageHeader
                title="Add location"
                description="Create a delivery location and configure its shipping fee."
                backLabel="Back to locations"
                onBack={() =>
                    navigate(
                        "/locations",
                    )
                }
            />

            <LocationForm
                isSubmitting={
                    createMutation.isPending
                }
                submitLabel="Create location"
                onCancel={() =>
                    navigate(
                        "/locations",
                    )
                }
                onSubmit={async (
                    values,
                ) => {
                    await createMutation.mutateAsync(
                        values,
                    );

                    navigate(
                        "/locations",
                    );
                }}
            />
        </div>
    );
}