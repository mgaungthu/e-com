import {
    useNavigate,
    useParams,
} from "react-router-dom";

import FullPageLoader from "@/components/FullPageLoader";
import PageHeader from "@/components/PageHeader";

import LocationForm from "@/features/locations/components/LocationForm";

import { useLocation } from "@/features/locations/hooks/useLocation";

import { useUpdateLocation } from "@/features/locations/hooks/useLocationMutations";

export default function LocationEditPage() {
    const navigate =
        useNavigate();

    const params =
        useParams();

    const locationId =
        Number(
            params.locationId,
        );

    const locationQuery =
        useLocation(
            Number.isFinite(
                locationId,
            )
                ? locationId
                : null,
        );

    const updateMutation =
        useUpdateLocation(
            locationId,
        );

    if (
        !Number.isFinite(
            locationId,
        )
    ) {
        return (
            <div className="rounded-xl border border-red-200 bg-red-50 p-6 text-red-700">
                Invalid location ID.
            </div>
        );
    }

    if (
        locationQuery.isLoading
    ) {
        return (
            <FullPageLoader />
        );
    }

    if (
        locationQuery.isError ||
        !locationQuery.data
    ) {
        return (
            <div className="rounded-xl border border-red-200 bg-red-50 p-6">
                <p className="text-sm text-red-700">
                    Unable to load the location.
                </p>

                <button
                    type="button"
                    onClick={() =>
                        navigate(
                            "/locations",
                        )
                    }
                    className="mt-4 text-sm font-medium text-blue-600"
                >
                    Back to locations
                </button>
            </div>
        );
    }

    const location =
        locationQuery.data
            .data.location;

    return (
        <div className="space-y-6">
            <PageHeader
                title="Edit location"
                description={`Update ${location.name_en}.`}
                backLabel="Back to locations"
                onBack={() =>
                    navigate(
                        "/locations",
                    )
                }
            />

            <LocationForm
                key={
                    location.id
                }
                location={
                    location
                }
                isSubmitting={
                    updateMutation.isPending
                }
                submitLabel="Save changes"
                onCancel={() =>
                    navigate(
                        "/locations",
                    )
                }
                onSubmit={async (
                    values,
                ) => {
                    await updateMutation.mutateAsync(
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