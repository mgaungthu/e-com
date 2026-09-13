import type { HomeBannerFormValues } from "@/features/home-banners/types/homeBanner.types";

export const HOME_BANNER_FORM_INITIAL_VALUES:
    HomeBannerFormValues = {
        image: null,

        product_id: "",

        sort_order: "0",

        is_active: true,

        starts_at: "",
        ends_at: "",
    };