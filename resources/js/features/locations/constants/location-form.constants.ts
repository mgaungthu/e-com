import type { LocationFormValues } from "@/features/locations/types/location.types";

export const LOCATION_TYPE_OPTIONS = [
    {
        value: "region",
        label: "Region",
    },
    {
        value: "state",
        label: "State",
    },
    {
        value: "district",
        label: "District",
    },
    {
        value: "city",
        label: "City",
    },
    {
        value: "township",
        label: "Township",
    },
    {
        value: "sub_district",
        label: "Sub District",
    },
    {
        value: "ward",
        label: "Ward",
    },
    {
        value: "village_tract",
        label: "Village Tract",
    },
] as const;

export const LOCATION_FORM_INITIAL_VALUES: LocationFormValues = {
    parent_id: "",

    name_en: "",

    name_mm: "",

    type: "region",

    shipping_fee: "",

    is_active: true,

    sort_order: 0,
};

export const LOCATION_VALIDATION_FIELD_ORDER: Array<
    keyof LocationFormValues
> = [
    "name_en",
    "name_mm",
    "type",
    "parent_id",
    "shipping_fee",
    "sort_order",
    "is_active",
];

export function getLocationTypeLabel(
    type: string,
): string {
    return (
        LOCATION_TYPE_OPTIONS.find(
            (option) =>
                option.value === type,
        )?.label ?? type
    );
}