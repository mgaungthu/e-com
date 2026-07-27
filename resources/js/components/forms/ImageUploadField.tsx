import {
    useRef,
    type ChangeEvent,
    type KeyboardEvent,
} from "react";

import {
    ImageIcon,
    ImagePlus,
    Trash2,
    UploadCloud,
} from "lucide-react";

import { FieldError } from "@/components/forms/FieldError";

export type ImageUploadFieldProps = {
    id: string;
    title?: string;
    description?: string;
    previewUrl: string | null;
    file?: File | null;
    error?: string | null;
    disabled?: boolean;
    accept?: string;
    emptyTitle?: string;
    helperText?: string;
    replaceLabel?: string;
    removeLabel?: string;
    previewAlt?: string;
    previewClassName?: string;
    className?: string;
    layout?: "side-by-side" | "stacked";
    onChange: (file: File | null) => void;
    onRemove: () => void;
};

export function ImageUploadField({
    id,
    title,
    description,
    previewUrl,
    file,
    error,
    disabled = false,
    accept = "image/jpeg,image/png,image/webp",
    emptyTitle = "Click to upload an image",
    helperText = "PNG, JPG or WebP",
    replaceLabel = "Replace image",
    removeLabel = "Remove image",
    previewAlt = "Image preview",
    previewClassName = "",
    className = "",
    layout = "side-by-side",
    onChange,
    onRemove,
}: ImageUploadFieldProps) {
    const inputRef = useRef<HTMLInputElement>(null);
    const errorId = `${id}-error`;

    function handleChange(event: ChangeEvent<HTMLInputElement>) {
        onChange(event.target.files?.item(0) ?? null);
        event.target.value = "";
    }

    function openFilePicker() {
        if (!disabled) {
            inputRef.current?.click();
        }
    }

    function handleUploadKeyDown(event: KeyboardEvent<HTMLDivElement>) {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            openFilePicker();
        }
    }

    const fileInput = (
        <input
            ref={inputRef}
            id={id}
            type="file"
            accept={accept}
            disabled={disabled}
            onChange={handleChange}
            className="sr-only"
            tabIndex={-1}
        />
    );

    if (layout === "stacked") {
        return (
            <div className={className}>
                {title ? (
                    <h3 className="text-lg font-semibold text-slate-900">
                        {title}
                    </h3>
                ) : null}

                {description ? (
                    <p className="mt-1 text-sm text-slate-500">
                        {description}
                    </p>
                ) : null}

                <div className={title || description ? "mt-5" : undefined}>
                    <div
                        data-form-field="image"
                        tabIndex={-1}
                        aria-invalid={Boolean(error)}
                        aria-describedby={error ? errorId : undefined}
                        className="outline-none"
                    >
                        {previewUrl ? (
                            <div
                                className={[
                                    "relative overflow-hidden rounded-xl border bg-slate-50",
                                    error
                                        ? "border-red-400"
                                        : "border-slate-200",
                                ].join(" ")}
                            >
                                <img
                                    src={previewUrl}
                                    alt={previewAlt}
                                    className={[
                                        "aspect-square w-full object-cover",
                                        previewClassName,
                                    ]
                                        .filter(Boolean)
                                        .join(" ")}
                                />

                                <button
                                    type="button"
                                    onClick={onRemove}
                                    disabled={disabled}
                                    className="absolute right-3 top-3 rounded-full bg-white p-2 text-slate-700 shadow transition hover:bg-red-50 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-50"
                                    aria-label={removeLabel}
                                >
                                    <Trash2 size={16} />
                                </button>
                            </div>
                        ) : (
                            <div
                                role="button"
                                tabIndex={disabled ? -1 : 0}
                                onClick={openFilePicker}
                                onKeyDown={handleUploadKeyDown}
                                className={[
                                    "flex aspect-square flex-col items-center justify-center rounded-xl border-2 border-dashed bg-slate-50 text-center outline-none transition",
                                    disabled
                                        ? "cursor-not-allowed opacity-60"
                                        : "cursor-pointer hover:border-blue-400 hover:bg-blue-50 focus:border-blue-500 focus:ring-2 focus:ring-blue-100",
                                    error
                                        ? "border-red-400"
                                        : "border-slate-300",
                                ].join(" ")}
                            >
                                <ImagePlus size={32} className="text-slate-400" />
                                <span className="mt-3 text-sm font-medium text-slate-700">
                                    {emptyTitle}
                                </span>
                                <span className="mt-1 text-xs text-slate-500">
                                    {helperText}
                                </span>
                            </div>
                        )}
                    </div>

                    {fileInput}

                    {previewUrl ? (
                        <button
                            type="button"
                            onClick={openFilePicker}
                            disabled={disabled}
                            className="mt-3 text-sm font-medium text-blue-600 transition hover:text-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {replaceLabel}
                        </button>
                    ) : null}

                    {file ? (
                        <p className="mt-2 break-all text-xs text-slate-500">
                            Selected: {file.name}
                        </p>
                    ) : null}

                    <FieldError
                        id={errorId}
                        message={error}
                        className="mt-2 text-sm"
                    />
                </div>
            </div>
        );
    }

    return (
        <div className={className}>
            <div className="grid gap-5 lg:grid-cols-[220px_1fr]">
                <div
                    className={[
                        "flex aspect-square w-full max-w-[220px] items-center justify-center overflow-hidden rounded-xl border bg-slate-50",
                        error ? "border-red-400" : "border-slate-200",
                        previewClassName,
                    ]
                        .filter(Boolean)
                        .join(" ")}
                >
                    {previewUrl ? (
                        <img
                            src={previewUrl}
                            alt={previewAlt}
                            className="h-full w-full object-cover"
                        />
                    ) : (
                        <div className="flex flex-col items-center px-5 text-center text-slate-400">
                            <ImageIcon size={36} />
                            <p className="mt-3 text-sm font-medium text-slate-500">
                                No image selected
                            </p>
                        </div>
                    )}
                </div>

                <div className="flex flex-col justify-center">
                    <div
                        data-form-field="image"
                        role="button"
                        tabIndex={disabled ? -1 : 0}
                        aria-disabled={disabled}
                        aria-invalid={Boolean(error)}
                        aria-describedby={error ? errorId : undefined}
                        onClick={openFilePicker}
                        onKeyDown={handleUploadKeyDown}
                        className={[
                            "flex flex-col items-center justify-center rounded-xl border-2 border-dashed px-6 py-8 text-center outline-none transition",
                            disabled
                                ? "cursor-not-allowed opacity-60"
                                : "cursor-pointer focus:ring-2 focus:ring-blue-100",
                            error
                                ? "border-red-300 bg-red-50/30 hover:border-red-400"
                                : "border-slate-300 hover:border-blue-400 hover:bg-blue-50/40",
                        ].join(" ")}
                    >
                        <div className="flex h-11 w-11 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                            <UploadCloud size={22} />
                        </div>

                        <p className="mt-3 text-sm font-semibold text-slate-700">
                            {previewUrl ? replaceLabel : emptyTitle}
                        </p>

                        <p className="mt-1 text-xs text-slate-500">
                            {helperText}
                        </p>
                    </div>

                    {fileInput}

                    {file ? (
                        <p className="mt-2 break-all text-xs text-slate-500">
                            Selected: {file.name}
                        </p>
                    ) : null}

                    <FieldError id={errorId} message={error} />

                    {previewUrl ? (
                        <button
                            type="button"
                            onClick={onRemove}
                            disabled={disabled}
                            className="mt-3 inline-flex w-fit items-center gap-2 rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Trash2 size={16} />
                            {removeLabel}
                        </button>
                    ) : null}
                </div>
            </div>
        </div>
    );
}
