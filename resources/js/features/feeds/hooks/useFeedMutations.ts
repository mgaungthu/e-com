import { useMutation, useQueryClient } from "@tanstack/react-query";
import { feedApi } from "@/features/feeds/api/feed.api";
import { feedKeys } from "@/features/feeds/api/feed.keys";
import type { FeedValues } from "@/features/feeds/types/feed.types";
export const useCreateFeed = () => { const client = useQueryClient(); return useMutation({ mutationFn: (values: FeedValues) => feedApi.create(values), onSuccess: () => client.invalidateQueries({ queryKey: feedKeys.lists() }) }); };
export const useUpdateFeed = (id: number) => { const client = useQueryClient(); return useMutation({ mutationFn: (values: FeedValues) => feedApi.update(id, values), onSuccess: () => Promise.all([client.invalidateQueries({ queryKey: feedKeys.lists() }), client.invalidateQueries({ queryKey: feedKeys.detail(id) })]) }); };
export const useDeleteFeed = () => { const client = useQueryClient(); return useMutation({ mutationFn: feedApi.remove, onSuccess: () => client.invalidateQueries({ queryKey: feedKeys.lists() }) }); };
