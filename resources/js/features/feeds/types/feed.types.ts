export type FeedMedia = { id: number; type: "image"; url: string; sort_order: number };
export type FeedProduct = { id: number; name: string; slug: string; price: string; sale_price: string | null; image_url: string | null };
export type Feed = { id: number; caption: string | null; status: "draft" | "published" | "archived"; is_active: boolean; published_at: string | null; media: FeedMedia[]; products: FeedProduct[]; likes_count: number; comments_count: number; updated_at: string };
export type FeedValues = { caption: string; status: Feed["status"]; is_active: boolean; published_at: string; images: File[]; remove_media_ids: number[]; media_order: number[]; products: { product_id: number; sort_order: number }[] };
export type FeedPagination = { data: Feed[]; current_page: number; from: number | null; last_page: number; per_page: number; to: number | null; total: number };
