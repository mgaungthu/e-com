import { useState } from "react";

import axios from "axios";
import { Navigate, useNavigate, useParams } from "react-router-dom";

import PageHeader from "@/components/PageHeader";
import { FeedForm } from "@/features/feeds/components/FeedForm";
import { useFeed } from "@/features/feeds/hooks/useFeeds";
import { useUpdateFeed } from "@/features/feeds/hooks/useFeedMutations";

export default function FeedEditPage() {
    const feedId = Number(useParams().feedId);
    const navigate = useNavigate();
    const query = useFeed(feedId);
    const mutation = useUpdateFeed(feedId);
    const [errors, setErrors] = useState<Record<string, string[]>>({});
    const [formError, setFormError] = useState<string | null>(null);

    if (!Number.isFinite(feedId)) return <Navigate to="/feeds" replace />;
    if (query.isLoading) return <div className="text-sm text-slate-500">Loading feed post...</div>;
    if (query.isError || !query.data?.data.feed) return <div className="text-sm text-red-600">Feed post could not be loaded.</div>;

    return <div className="space-y-6"><PageHeader title="Edit Feed Post" description="Update post content, images, tagged products, and publishing status." backLabel="Back to feeds" onBack={() => navigate("/feeds")} /><FeedForm feed={query.data.data.feed} submitting={mutation.isPending} validationErrors={errors} formError={formError} onCancel={() => navigate("/feeds")} onSubmit={async (values) => { setErrors({}); setFormError(null); try { await mutation.mutateAsync(values); navigate("/feeds"); } catch (error) { if (axios.isAxiosError(error)) { const data = error.response?.data as { message?: string; errors?: Record<string, string[]> }; setErrors(data?.errors ?? {}); setFormError(data?.message ?? "Unable to update feed post."); return; } setFormError("An unexpected error occurred."); } }} /></div>;
}
