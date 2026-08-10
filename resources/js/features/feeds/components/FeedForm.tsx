import { useEffect, useMemo, useState } from "react";

import {
    Check,
    ImagePlus,
    Images,
    Package,
    Send,
    X,
} from "lucide-react";

import { FormActions } from "@/components/forms/FormActions";
import { FormErrorAlert } from "@/components/forms/FormErrorAlert";
import { FormSection } from "@/components/forms/FormSection";
import type {
    Feed,
    FeedValues,
} from "@/features/feeds/types/feed.types";
import { useProducts } from "@/features/products/hooks/useProducts";

type ValidationErrors = Record<string, string[]>;

const initial: FeedValues = {
    caption: "",
    status: "draft",
    is_active: true,
    published_at: "",
    images: [],
    remove_media_ids: [],
    media_order: [],
    products: [],
};

export function FeedForm({
    feed,
    submitting,
    validationErrors = {},
    formError = null,
    onCancel,
    onSubmit,
}: {
    feed?: Feed;
    submitting: boolean;
    validationErrors?: ValidationErrors;
    formError?: string | null;
    onCancel: () => void;
    onSubmit: (values: FeedValues) => void;
}) {
    const [values, setValues] = useState<FeedValues>(initial);
    const [productSearch, setProductSearch] = useState("");

    const productsQuery = useProducts({
        page: 1,
        per_page: 10,
        search: productSearch || undefined,
        status: "active",
    });

    useEffect(() => {
        if (!feed) {
            setValues(initial);
            return;
        }

        setValues({
            ...initial,
            caption: feed.caption ?? "",
            status: feed.status,
            is_active: feed.is_active,
            published_at:
                feed.published_at?.slice(0, 16) ?? "",
            media_order: feed.media.map(
                (media) => media.id,
            ),
            products: feed.products.map(
                (product, index) => ({
                    product_id: product.id,
                    sort_order: index,
                }),
            ),
        });
    }, [feed]);

    const existingMedia = useMemo(() => {
        const media = (feed?.media ?? []).filter(
            (item) =>
                !values.remove_media_ids.includes(
                    item.id,
                ),
        );

        return [...media].sort((left, right) => {
            const leftIndex =
                values.media_order.indexOf(left.id);
            const rightIndex =
                values.media_order.indexOf(right.id);

            if (
                leftIndex === -1 &&
                rightIndex === -1
            ) {
                return left.id - right.id;
            }

            if (leftIndex === -1) {
                return 1;
            }

            if (rightIndex === -1) {
                return -1;
            }

            return leftIndex - rightIndex;
        });
    }, [
        feed,
        values.media_order,
        values.remove_media_ids,
    ]);

    const availableProducts =
        productsQuery.data?.data.data ?? [];

    const selectedProducts = values.products
        .map(({ product_id }) =>
            [
                ...(feed?.products ?? []),
                ...availableProducts,
            ].find(
                (product) =>
                    product.id === product_id,
            ),
        )
        .filter(
            (
                product,
            ): product is NonNullable<typeof product> =>
                Boolean(product),
        );

    function moveMedia(
        index: number,
        direction: -1 | 1,
    ) {
        const order = existingMedia.map(
            (media) => media.id,
        );

        const targetIndex = index + direction;

        if (
            targetIndex < 0 ||
            targetIndex >= order.length
        ) {
            return;
        }

        [order[index], order[targetIndex]] = [
            order[targetIndex],
            order[index],
        ];

        setValues((current) => ({
            ...current,
            media_order: order,
        }));
    }

    function addProduct(productId: number) {
        setValues((current) => {
            const alreadySelected =
                current.products.some(
                    (product) =>
                        product.product_id ===
                        productId,
                );

            if (alreadySelected) {
                return current;
            }

            return {
                ...current,
                products: [
                    ...current.products,
                    {
                        product_id: productId,
                        sort_order:
                            current.products.length,
                    },
                ],
            };
        });
    }

    function removeProduct(productId: number) {
        setValues((current) => ({
            ...current,
            products: current.products
                .filter(
                    (product) =>
                        product.product_id !==
                        productId,
                )
                .map((product, index) => ({
                    ...product,
                    sort_order: index,
                })),
        }));
    }

    function removeExistingMedia(
        mediaId: number,
    ) {
        setValues((current) => ({
            ...current,

            remove_media_ids:
                current.remove_media_ids.includes(
                    mediaId,
                )
                    ? current.remove_media_ids
                    : [
                          ...current.remove_media_ids,
                          mediaId,
                      ],

            media_order:
                current.media_order.filter(
                    (id) => id !== mediaId,
                ),
        }));
    }

    function addImages(
        files: FileList | null,
    ) {
        if (!files?.length) {
            return;
        }

        const newImages =
            Array.from(files);

        setValues((current) => ({
            ...current,
            images: [
                ...current.images,
                ...newImages,
            ],
        }));
    }

    function removeNewImage(
        index: number,
    ) {
        setValues((current) => ({
            ...current,
            images: current.images.filter(
                (_, itemIndex) =>
                    itemIndex !== index,
            ),
        }));
    }

    return (
        <form
            className="space-y-6"
            noValidate
            onSubmit={(event) => {
                event.preventDefault();

                onSubmit(values);
            }}
        >
            <FormErrorAlert
                message={formError}
            />

            {/* Images */}
            <FormSection
                title="Images"
                description="Upload JPG, PNG, or WEBP images. Video uploads are not supported."
                icon={<Images size={20} />}
            >
                <label className="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 px-6 py-8 text-center transition hover:border-blue-400 hover:bg-blue-50/40">
                    <ImagePlus
                        size={24}
                        className="text-blue-600"
                    />

                    <span className="mt-3 text-sm font-semibold text-slate-700">
                        Add images
                    </span>

                    <span className="mt-1 text-xs text-slate-500">
                        JPG, PNG, WEBP · up to
                        5 MB each
                    </span>

                    <input
                        type="file"
                        className="sr-only"
                        accept="image/jpeg,image/png,image/webp"
                        multiple
                        onChange={(event) => {
                            addImages(
                                event.target.files,
                            );

                            event.target.value =
                                "";
                        }}
                    />
                </label>

                {validationErrors.images?.[0] ? (
                    <p className="mt-2 text-sm text-red-600">
                        {
                            validationErrors
                                .images[0]
                        }
                    </p>
                ) : null}

                {existingMedia.length > 0 ||
                values.images.length > 0 ? (
                    <div className="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                        {existingMedia.map(
                            (media, index) => (
                                <div
                                    key={
                                        media.id
                                    }
                                    className="group relative overflow-hidden rounded-lg border border-slate-200 bg-slate-50"
                                >
                                    <img
                                        src={
                                            media.url
                                        }
                                        alt="Feed media"
                                        className="aspect-square w-full object-cover"
                                    />

                                    <div className="flex items-center justify-between gap-2 p-2">
                                        <button
                                            type="button"
                                            disabled={
                                                index ===
                                                0
                                            }
                                            onClick={() =>
                                                moveMedia(
                                                    index,
                                                    -1,
                                                )
                                            }
                                            className="text-xs font-medium text-slate-600 disabled:cursor-not-allowed disabled:opacity-30"
                                        >
                                            ← Move
                                        </button>

                                        <button
                                            type="button"
                                            disabled={
                                                index ===
                                                existingMedia.length -
                                                    1
                                            }
                                            onClick={() =>
                                                moveMedia(
                                                    index,
                                                    1,
                                                )
                                            }
                                            className="text-xs font-medium text-slate-600 disabled:cursor-not-allowed disabled:opacity-30"
                                        >
                                            Move →
                                        </button>
                                    </div>

                                    <button
                                        type="button"
                                        onClick={() =>
                                            removeExistingMedia(
                                                media.id,
                                            )
                                        }
                                        className="absolute right-2 top-2 rounded-full bg-white/90 p-1.5 text-red-600 shadow transition hover:bg-red-50"
                                        aria-label="Remove image"
                                    >
                                        <X
                                            size={
                                                14
                                            }
                                        />
                                    </button>
                                </div>
                            ),
                        )}

                        {values.images.map(
                            (image, index) => (
                                <NewImagePreview
                                    key={`${image.name}-${image.size}-${image.lastModified}-${index}`}
                                    image={
                                        image
                                    }
                                    onRemove={() =>
                                        removeNewImage(
                                            index,
                                        )
                                    }
                                />
                            ),
                        )}
                    </div>
                ) : null}
            </FormSection>

            {/* Post content */}
            <FormSection
                title="Post content"
                description="Write the caption your customers will see in the feed."
                icon={<Send size={20} />}
            >
                <label className="block text-sm font-medium text-slate-700">
                    Caption

                    <textarea
                        value={values.caption}
                        rows={6}
                        maxLength={5000}
                        onChange={(event) =>
                            setValues(
                                (
                                    current,
                                ) => ({
                                    ...current,
                                    caption:
                                        event
                                            .target
                                            .value,
                                }),
                            )
                        }
                        placeholder="Share your product story, offer, or announcement..."
                        className="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    />

                    <div className="mt-1 flex items-start justify-between gap-4">
                        <span className="text-xs text-slate-500">
                            {
                                values.caption
                                    .length
                            }
                            /5000 characters
                        </span>

                        {validationErrors
                            .caption?.[0] ? (
                            <span className="text-sm text-red-600">
                                {
                                    validationErrors
                                        .caption[0]
                                }
                            </span>
                        ) : null}
                    </div>
                </label>
            </FormSection>

            {/* Tagged Products */}
            <FormSection
                title="Tagged products"
                description="Optional products displayed with this post."
                icon={<Package size={20} />}
            >
                <input
                    value={productSearch}
                    onChange={(event) =>
                        setProductSearch(
                            event.target.value,
                        )
                    }
                    placeholder="Search products to tag..."
                    className="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                />

                {productSearch ? (
                    <div className="mt-2 max-h-56 overflow-y-auto rounded-lg border border-slate-200 bg-white">
                        {productsQuery.isLoading ? (
                            <p className="px-3 py-4 text-sm text-slate-500">
                                Searching
                                products...
                            </p>
                        ) : null}

                        {!productsQuery.isLoading &&
                        availableProducts.length ===
                            0 ? (
                            <p className="px-3 py-4 text-sm text-slate-500">
                                No products
                                found.
                            </p>
                        ) : null}

                        {availableProducts.map(
                            (product) => {
                                const selected =
                                    values.products.some(
                                        (
                                            item,
                                        ) =>
                                            item.product_id ===
                                            product.id,
                                    );

                                return (
                                    <button
                                        type="button"
                                        key={
                                            product.id
                                        }
                                        onClick={() =>
                                            addProduct(
                                                product.id,
                                            )
                                        }
                                        disabled={
                                            selected
                                        }
                                        className="flex w-full items-center gap-3 border-b border-slate-100 px-3 py-3 text-left text-sm last:border-0 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                                    >
                                        {product.image_url ? (
                                            <img
                                                src={
                                                    product.image_url
                                                }
                                                alt={
                                                    product.name
                                                }
                                                className="h-10 w-10 rounded-md border border-slate-200 object-cover"
                                            />
                                        ) : (
                                            <div className="flex h-10 w-10 items-center justify-center rounded-md border border-slate-200 bg-slate-100">
                                                <Package
                                                    size={
                                                        16
                                                    }
                                                    className="text-slate-400"
                                                />
                                            </div>
                                        )}

                                        <div className="min-w-0 flex-1">
                                            <p className="truncate font-medium text-slate-800">
                                                {
                                                    product.name
                                                }
                                            </p>

                                            <p className="mt-0.5 text-xs text-slate-500">
                                                {
                                                    product.price
                                                }
                                            </p>
                                        </div>

                                        {selected ? (
                                            <Check
                                                size={
                                                    16
                                                }
                                                className="shrink-0 text-emerald-600"
                                            />
                                        ) : (
                                            <span className="shrink-0 text-xs font-medium text-blue-600">
                                                Add
                                            </span>
                                        )}
                                    </button>
                                );
                            },
                        )}
                    </div>
                ) : null}

                {selectedProducts.length > 0 ? (
                    <div className="mt-4 space-y-2">
                        {selectedProducts.map(
                            (
                                product,
                                index,
                            ) => (
                                <div
                                    key={
                                        product.id
                                    }
                                    className="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2.5"
                                >
                                    <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-500">
                                        {index +
                                            1}
                                    </span>

                                    {product.image_url ? (
                                        <img
                                            src={
                                                product.image_url
                                            }
                                            alt={
                                                product.name
                                            }
                                            className="h-10 w-10 shrink-0 rounded-md border border-slate-200 object-cover"
                                        />
                                    ) : (
                                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-slate-200 bg-slate-100">
                                            <Package
                                                size={
                                                    16
                                                }
                                                className="text-slate-400"
                                            />
                                        </div>
                                    )}

                                    <div className="min-w-0 flex-1">
                                        <p className="truncate text-sm font-medium text-slate-800">
                                            {
                                                product.name
                                            }
                                        </p>

                                        <p className="text-xs text-slate-500">
                                            {
                                                product.price
                                            }
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        onClick={() =>
                                            removeProduct(
                                                product.id,
                                            )
                                        }
                                        className="shrink-0 text-xs font-medium text-red-600 transition hover:text-red-700"
                                    >
                                        Remove
                                    </button>
                                </div>
                            ),
                        )}
                    </div>
                ) : (
                    <p className="mt-3 text-sm text-slate-500">
                        No products tagged
                        yet.
                    </p>
                )}

                {validationErrors
                    .products?.[0] ? (
                    <p className="mt-2 text-sm text-red-600">
                        {
                            validationErrors
                                .products[0]
                        }
                    </p>
                ) : null}
            </FormSection>

            {/* Publishing */}
            <FormSection
                title="Publishing"
                description="Control when and where this post is visible."
                icon={<Check size={20} />}
                contentClassName="grid gap-5 p-5 md:grid-cols-3"
            >
                <label className="text-sm font-medium text-slate-700">
                    Status

                    <select
                        value={values.status}
                        onChange={(event) =>
                            setValues(
                                (
                                    current,
                                ) => ({
                                    ...current,
                                    status: event
                                        .target
                                        .value as FeedValues["status"],
                                }),
                            )
                        }
                        className="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >
                        <option value="draft">
                            Draft
                        </option>

                        <option value="published">
                            Published
                        </option>

                        <option value="archived">
                            Archived
                        </option>
                    </select>

                    {validationErrors
                        .status?.[0] ? (
                        <span className="mt-1 block text-sm text-red-600">
                            {
                                validationErrors
                                    .status[0]
                            }
                        </span>
                    ) : null}
                </label>

                <label className="text-sm font-medium text-slate-700">
                    Published at

                    <input
                        value={
                            values.published_at
                        }
                        onChange={(event) =>
                            setValues(
                                (
                                    current,
                                ) => ({
                                    ...current,
                                    published_at:
                                        event
                                            .target
                                            .value,
                                }),
                            )
                        }
                        type="datetime-local"
                        className="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    />

                    {validationErrors
                        .published_at?.[0] ? (
                        <span className="mt-1 block text-sm text-red-600">
                            {
                                validationErrors
                                    .published_at[0]
                            }
                        </span>
                    ) : null}
                </label>

                <label className="flex items-center gap-3 self-end rounded-lg border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700">
                    <input
                        type="checkbox"
                        checked={
                            values.is_active
                        }
                        onChange={(event) =>
                            setValues(
                                (
                                    current,
                                ) => ({
                                    ...current,
                                    is_active:
                                        event
                                            .target
                                            .checked,
                                }),
                            )
                        }
                        className="h-4 w-4 rounded border-slate-300 text-blue-600"
                    />

                    Active and visible when
                    published
                </label>
            </FormSection>

            <FormActions
                submitLabel={
                    feed
                        ? "Update feed post"
                        : "Create feed post"
                }
                submittingLabel={
                    feed
                        ? "Updating..."
                        : "Creating..."
                }
                isSubmitting={submitting}
                onCancel={onCancel}
            />
        </form>
    );
}

function NewImagePreview({
    image,
    onRemove,
}: {
    image: File;
    onRemove: () => void;
}) {
    const [previewUrl, setPreviewUrl] =
        useState<string | null>(null);

    useEffect(() => {
        const objectUrl =
            URL.createObjectURL(image);

        setPreviewUrl(objectUrl);

        return () => {
            URL.revokeObjectURL(
                objectUrl,
            );
        };
    }, [image]);

    return (
        <div className="relative overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
            {previewUrl ? (
                <img
                    src={previewUrl}
                    alt={image.name}
                    className="aspect-square w-full object-cover"
                />
            ) : (
                <div className="aspect-square w-full animate-pulse bg-slate-100" />
            )}

            <p className="truncate px-2 py-2 text-xs text-slate-600">
                {image.name}
            </p>

            <button
                type="button"
                onClick={onRemove}
                className="absolute right-2 top-2 rounded-full bg-white/90 p-1.5 text-red-600 shadow transition hover:bg-red-50"
                aria-label={`Remove ${image.name}`}
            >
                <X size={14} />
            </button>
        </div>
    );
}