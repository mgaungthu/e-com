import { useQuery } from "@tanstack/react-query";
import { feedApi } from "@/features/feeds/api/feed.api";
import { feedKeys } from "@/features/feeds/api/feed.keys";
export const useFeeds = (filters: object) => useQuery({ queryKey: feedKeys.list(filters), queryFn: () => feedApi.list(filters) });
export const useFeed = (id: number) => useQuery({ queryKey: feedKeys.detail(id), queryFn: () => feedApi.show(id) });
