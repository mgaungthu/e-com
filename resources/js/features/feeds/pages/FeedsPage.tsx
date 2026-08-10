import { useMemo, useState } from "react";

import axios from "axios";
import { Edit3, ImageIcon, Plus, Search, Trash2 } from "lucide-react";
import { useNavigate } from "react-router-dom";

import { useDeleteFeed } from "@/features/feeds/hooks/useFeedMutations";
import { useFeeds } from "@/features/feeds/hooks/useFeeds";
import { useAuthStore } from "@/store/authStore";

type ErrorResponse = { message?: string };
type FeedStatus = "all" | "draft" | "published" | "archived";
type ActiveStatus = "all" | "active" | "inactive";

function statusClassName(status: FeedStatus) {
    return {
        draft: "bg-amber-50 text-amber-700",
        published: "bg-emerald-50 text-emerald-700",
        archived: "bg-slate-100 text-slate-600",
        all: "bg-slate-100 text-slate-600",
    }[status];
}

function formatDate(value: string | null) {
    return value
        ? new Intl.DateTimeFormat("en", {
              dateStyle: "medium",
              timeStyle: "short",
          }).format(new Date(value))
        : "—";
}

export default function FeedsPage() {
    const [page, setPage] = useState(1);
    const [search, setSearch] = useState("");
    const [status, setStatus] = useState<FeedStatus>("all");
    const [activeStatus, setActiveStatus] = useState<ActiveStatus>("all");
    const navigate = useNavigate();
    const permissions = useAuthStore((state) => state.user?.permissions ?? []);
    const canCreate = permissions.includes("feeds.create");
    const canUpdate = permissions.includes("feeds.update");
    const canDelete = permissions.includes("feeds.delete");

    const filters = useMemo(
        () => ({
            page,
            per_page: 15,
            search: search || undefined,
            status: status === "all" ? undefined : status,
            is_active:
                activeStatus === "all"
                    ? undefined
                    : activeStatus === "active" ? 1 : 0,
        }),
        [activeStatus, page, search, status],
    );
    const feedsQuery = useFeeds(filters);
    const deleteMutation = useDeleteFeed();
    const pagination = feedsQuery.data?.data;
    const feeds = pagination?.data ?? [];

    async function handleDelete(feedId: number, caption: string | null) {
        if (!window.confirm(`Delete "${caption || "this"}" feed post?`)) return;

        try {
            await deleteMutation.mutateAsync(feedId);
        } catch (error) {
            window.alert(
                axios.isAxiosError<ErrorResponse>(error)
                    ? error.response?.data.message ?? "Unable to delete feed post."
                    : "Unable to delete feed post.",
            );
        }
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-slate-900">Feeds</h1>
                    <p className="mt-1 text-sm text-slate-500">Manage image-only social commerce posts and tagged products.</p>
                </div>
                {canCreate ? <button type="button" onClick={() => navigate("/feeds/create")} className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"><Plus size={18} />Create feed</button> : null}
            </div>

            <div className="rounded-xl border border-slate-200 bg-white">
                <div className="flex flex-col gap-3 border-b border-slate-200 p-4 lg:flex-row">
                    <div className="relative flex-1"><Search size={18} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" /><input type="search" value={search} onChange={(event) => { setSearch(event.target.value); setPage(1); }} placeholder="Search feed captions..." className="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100" /></div>
                    <select value={status} onChange={(event) => { setStatus(event.target.value as FeedStatus); setPage(1); }} className="rounded-lg border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"><option value="all">All statuses</option><option value="draft">Draft</option><option value="published">Published</option><option value="archived">Archived</option></select>
                    <select value={activeStatus} onChange={(event) => { setActiveStatus(event.target.value as ActiveStatus); setPage(1); }} className="rounded-lg border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"><option value="all">Active and inactive</option><option value="active">Active</option><option value="inactive">Inactive</option></select>
                </div>

                {feedsQuery.isLoading ? <div className="p-10 text-center text-sm text-slate-500">Loading feeds...</div> : feedsQuery.isError ? <div className="p-10 text-center"><p className="text-sm text-red-600">Unable to load feeds.</p><button type="button" onClick={() => void feedsQuery.refetch()} className="mt-3 text-sm font-medium text-blue-600 transition hover:text-blue-700">Try again</button></div> : feeds.length === 0 ? <div className="p-10 text-center"><div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400"><ImageIcon size={22} /></div><p className="mt-4 text-sm font-medium text-slate-700">No feed posts found.</p><p className="mt-1 text-sm text-slate-500">Create your first social commerce post to get started.</p></div> : <><div className="overflow-x-auto"><table className="min-w-full divide-y divide-slate-200"><thead className="bg-slate-50"><tr>{["Preview", "Caption", "Images", "Tagged products", "Status", "Published at", "Likes", "Comments", "Updated at"].map((heading) => <th key={heading} className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{heading}</th>)}{canUpdate || canDelete ? <th className="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th> : null}</tr></thead><tbody className="divide-y divide-slate-100 bg-white">{feeds.map((feed) => <tr key={feed.id} className="transition hover:bg-slate-50"><td className="px-5 py-4">{feed.media[0] ? <img src={feed.media[0].url} alt="Feed preview" className="h-11 w-11 rounded-lg object-cover" /> : <div className="flex h-11 w-11 items-center justify-center rounded-lg bg-slate-100 text-slate-400"><ImageIcon size={19} /></div>}</td><td className="max-w-xs px-5 py-4"><p className="truncate font-medium text-slate-900">{feed.caption || "Untitled post"}</p><p className="mt-0.5 text-xs text-slate-500">{feed.is_active ? "Active" : "Inactive"}</p></td><td className="px-5 py-4 text-sm text-slate-600">{feed.media.length}</td><td className="px-5 py-4 text-sm text-slate-600">{feed.products.length}</td><td className="px-5 py-4"><span className={["inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize", statusClassName(feed.status)].join(" ")}>{feed.status}</span></td><td className="whitespace-nowrap px-5 py-4 text-sm text-slate-600">{formatDate(feed.published_at)}</td><td className="px-5 py-4 text-sm text-slate-600">{feed.likes_count}</td><td className="px-5 py-4 text-sm text-slate-600">{feed.comments_count}</td><td className="whitespace-nowrap px-5 py-4 text-sm text-slate-600">{formatDate(feed.updated_at)}</td>{canUpdate || canDelete ? <td className="px-5 py-4"><div className="flex justify-end gap-2">{canUpdate ? <button type="button" onClick={() => navigate(`/feeds/${feed.id}/edit`)} className="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:bg-slate-100" aria-label={`Edit ${feed.caption || "feed post"}`}><Edit3 size={16} /></button> : null}{canDelete ? <button type="button" onClick={() => void handleDelete(feed.id, feed.caption)} disabled={deleteMutation.isPending} className="rounded-lg border border-red-200 p-2 text-red-600 transition hover:bg-red-50 disabled:opacity-50" aria-label={`Delete ${feed.caption || "feed post"}`}><Trash2 size={16} /></button> : null}</div></td> : null}</tr>)}</tbody></table></div><div className="flex items-center justify-between border-t border-slate-200 px-5 py-4"><p className="text-sm text-slate-500">Showing {pagination?.from ?? 0}–{pagination?.to ?? 0} of {pagination?.total ?? 0}</p><div className="flex gap-2"><button type="button" disabled={!pagination || pagination.current_page <= 1} onClick={() => setPage((current) => Math.max(current - 1, 1))} className="rounded-lg border px-3 py-2 text-sm disabled:cursor-not-allowed disabled:opacity-50">Previous</button><button type="button" disabled={!pagination || pagination.current_page >= pagination.last_page} onClick={() => setPage((current) => current + 1)} className="rounded-lg border px-3 py-2 text-sm disabled:cursor-not-allowed disabled:opacity-50">Next</button></div></div></>}
            </div>
        </div>
    );
}
