import { useEffect, useRef, useState } from "react";

import {
    ArrowLeft,
    ArrowRight,
    ImagePlus,
    Star,
    Trash2,
} from "lucide-react";

import type { ProductImage } from "@/features/products/types/product.types";

type ProductImageGalleryFieldProps = {
    existingImages: ProductImage[];
    newImages: File[];
    primaryImageId: number | null;
    primaryNewImageIndex: number | null;
    error?: string | null;
    disabled?: boolean;
    onAdd: (files: File[]) => void;
    onRemoveExisting: (imageId: number) => void;
    onRemoveNew: (index: number) => void;
    onSetPrimaryExisting: (imageId: number) => void;
    onSetPrimaryNew: (index: number) => void;
    onMoveExisting: (index: number, direction: -1 | 1) => void;
    onMoveNew: (index: number, direction: -1 | 1) => void;
};

const MAX_IMAGES = 10;

export function ProductImageGalleryField({
    existingImages,
    newImages,
    primaryImageId,
    primaryNewImageIndex,
    error,
    disabled = false,
    onAdd,
    onRemoveExisting,
    onRemoveNew,
    onSetPrimaryExisting,
    onSetPrimaryNew,
    onMoveExisting,
    onMoveNew,
}: ProductImageGalleryFieldProps) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [newImageUrls, setNewImageUrls] = useState<string[]>([]);
    const totalImages = existingImages.length + newImages.length;

    useEffect(() => {
        const urls = newImages.map((file) => URL.createObjectURL(file));
        setNewImageUrls(urls);

        return () => {
            urls.forEach((url) => URL.revokeObjectURL(url));
        };
    }, [newImages]);

    return (
        <div className="space-y-4 p-5">
            <div
                className={[
                    "rounded-xl border-2 border-dashed p-6 text-center transition",
                    error
                        ? "border-red-300 bg-red-50/40"
                        : "border-slate-300 bg-slate-50",
                ].join(" ")}
            >
                <input
                    ref={inputRef}
                    type="file"
                    multiple
                    accept="image/jpeg,image/png,image/webp"
                    className="sr-only"
                    disabled={disabled || totalImages >= MAX_IMAGES}
                    onChange={(event) => {
                        const files = Array.from(event.target.files ?? []);
                        onAdd(files.slice(0, MAX_IMAGES - totalImages));
                        event.target.value = "";
                    }}
                />
                <ImagePlus size={30} className="mx-auto text-slate-400" />
                <p className="mt-3 text-sm font-semibold text-slate-700">
                    Upload product images
                </p>
                <p className="mt-1 text-xs text-slate-500">
                    JPG, PNG or WebP · up to 5 MB each · maximum {MAX_IMAGES}
                </p>
                <button
                    type="button"
                    disabled={disabled || totalImages >= MAX_IMAGES}
                    onClick={() => inputRef.current?.click()}
                    className="mt-4 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Choose images
                </button>
                <p className="mt-2 text-xs text-slate-400">
                    {totalImages} of {MAX_IMAGES} images selected
                </p>
            </div>

            {error ? <p className="text-sm font-medium text-red-600">{error}</p> : null}

            {totalImages > 0 ? (
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {existingImages.map((image, index) => (
                        <ImageCard
                            key={`existing-${image.id}`}
                            src={image.url}
                            alt={image.alt_text ?? "Product image"}
                            isPrimary={primaryImageId === image.id}
                            disabled={disabled}
                            canMoveLeft={index > 0}
                            canMoveRight={index < existingImages.length - 1}
                            onSetPrimary={() => onSetPrimaryExisting(image.id)}
                            onRemove={() => onRemoveExisting(image.id)}
                            onMoveLeft={() => onMoveExisting(index, -1)}
                            onMoveRight={() => onMoveExisting(index, 1)}
                        />
                    ))}

                    {newImages.map((file, index) => (
                        <ImageCard
                            key={`new-${file.name}-${file.lastModified}-${index}`}
                            src={newImageUrls[index]}
                            alt={file.name}
                            label="New"
                            isPrimary={primaryNewImageIndex === index}
                            disabled={disabled}
                            canMoveLeft={index > 0}
                            canMoveRight={index < newImages.length - 1}
                            onSetPrimary={() => onSetPrimaryNew(index)}
                            onRemove={() => onRemoveNew(index)}
                            onMoveLeft={() => onMoveNew(index, -1)}
                            onMoveRight={() => onMoveNew(index, 1)}
                        />
                    ))}
                </div>
            ) : (
                <p className="text-center text-sm text-slate-500">
                    No product images selected.
                </p>
            )}
        </div>
    );
}

type ImageCardProps = {
    src?: string;
    alt: string;
    label?: string;
    isPrimary: boolean;
    disabled: boolean;
    canMoveLeft: boolean;
    canMoveRight: boolean;
    onSetPrimary: () => void;
    onRemove: () => void;
    onMoveLeft: () => void;
    onMoveRight: () => void;
};

function ImageCard({
    src,
    alt,
    label,
    isPrimary,
    disabled,
    canMoveLeft,
    canMoveRight,
    onSetPrimary,
    onRemove,
    onMoveLeft,
    onMoveRight,
}: ImageCardProps) {
    return (
        <div className="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div className="relative aspect-square bg-slate-100">
                {src ? (
                    <img src={src} alt={alt} className="h-full w-full object-cover" />
                ) : null}
                <div className="absolute left-2 top-2 flex gap-2">
                    {isPrimary ? (
                        <span className="inline-flex items-center gap-1 rounded-full bg-amber-400 px-2.5 py-1 text-xs font-bold text-amber-950 shadow-sm">
                            <Star size={12} fill="currentColor" /> Primary
                        </span>
                    ) : null}
                    {label ? (
                        <span className="rounded-full bg-blue-600 px-2.5 py-1 text-xs font-bold text-white shadow-sm">
                            {label}
                        </span>
                    ) : null}
                </div>
            </div>
            <div className="flex items-center justify-between gap-2 p-3">
                <div className="flex gap-1">
                    <button
                        type="button"
                        disabled={disabled || !canMoveLeft}
                        onClick={onMoveLeft}
                        className="rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 disabled:opacity-30"
                        aria-label="Move image left"
                    >
                        <ArrowLeft size={14} />
                    </button>
                    <button
                        type="button"
                        disabled={disabled || !canMoveRight}
                        onClick={onMoveRight}
                        className="rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 disabled:opacity-30"
                        aria-label="Move image right"
                    >
                        <ArrowRight size={14} />
                    </button>
                </div>
                <div className="flex gap-1">
                    {!isPrimary ? (
                        <button
                            type="button"
                            disabled={disabled}
                            onClick={onSetPrimary}
                            className="rounded-lg border border-amber-200 p-2 text-amber-600 hover:bg-amber-50 disabled:opacity-50"
                            aria-label="Set as primary image"
                        >
                            <Star size={14} />
                        </button>
                    ) : null}
                    <button
                        type="button"
                        disabled={disabled}
                        onClick={onRemove}
                        className="rounded-lg border border-red-200 p-2 text-red-600 hover:bg-red-50 disabled:opacity-50"
                        aria-label="Remove image"
                    >
                        <Trash2 size={14} />
                    </button>
                </div>
            </div>
        </div>
    );
}
