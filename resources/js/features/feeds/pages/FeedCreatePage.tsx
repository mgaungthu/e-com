import { useState } from "react";

import axios from "axios";
import { useNavigate } from "react-router-dom";

import PageHeader from "@/components/PageHeader";
import { FeedForm } from "@/features/feeds/components/FeedForm";
import { useCreateFeed } from "@/features/feeds/hooks/useFeedMutations";

export default function FeedCreatePage() {
    const navigate = useNavigate();
    const mutation = useCreateFeed();
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [formError, setFormError] = useState<string | null>(null);

    return (
        <div className="space-y-6">
            <PageHeader
                title="Create Feed Post"
                description="Create an image-first social commerce post for customers."
                backLabel="Back to feeds"
                onBack={() => navigate("/feeds")}
            />
            <FeedForm
                submitting={mutation.isPending}
                validationErrors={errors}
                formError={formError}
                onCancel={() => navigate("/feeds")}
                onSubmit={async (values) => {
                    setErrors({});
                    setFormError(null);
                    try {
                        await mutation.mutateAsync(values);
                        navigate("/feeds");
                    } catch (error) {
                        if (axios.isAxiosError(error)) {
                            const data = error.response?.data as {
                                message?: string;
                                errors?: Record<string, string[]>;
                            };
                            setErrors(data?.errors ?? {});
                            setFormError(
                                data?.message ?? "Unable to create feed post.",
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
