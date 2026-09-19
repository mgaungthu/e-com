import {
    ImageIcon,
    RotateCcw,
    Trash2,
    Upload,
} from "lucide-react";
import {
    useEffect,
    useState,
} from "react";

type PaymentMethodImageFieldProps = {
    id: string;
    fieldName: string;

    label: string;
    description?: string;

    currentImageUrl?: string | null;

    file: File | null;
    removeExisting: boolean;

    disabled?: boolean;
    error?: string | null;

    onChange: (file: File | null) => void;

    onRemoveExistingChange: (
        remove: boolean,
    ) => void;
};

export function PaymentMethodImageField({
    id,
    fieldName,
    label,
    description,
    currentImageUrl = null,
    file,
    removeExisting,
    disabled = false,
    error = null,
    onChange,
    onRemoveExistingChange,
}: PaymentMethodImageFieldProps) {
    const [previewUrl, setPreviewUrl] =
        useState<string | null>(null);

    useEffect(() => {
        if (!file) {
            setPreviewUrl(null);

            return;
        }

        const objectUrl =
            URL.createObjectURL(file);

        setPreviewUrl(objectUrl);

        return () => {
            URL.revokeObjectURL(
                objectUrl,
            );
        };
    }, [file]);

    const displayedImageUrl =
        previewUrl ??
        (
            removeExisting
                ? null
                : currentImageUrl
        );

    function handleFileChange(
        event: React.ChangeEvent<HTMLInputElement>,
    ) {
        const selectedFile =
            event.target.files?.[0] ??
            null;

        if (!selectedFile) {
            return;
        }

        onRemoveExistingChange(false);
        onChange(selectedFile);

        event.target.value = "";
    }

    function handleRemove() {
        if (file) {
            onChange(null);

            return;
        }

        if (currentImageUrl) {
            onRemoveExistingChange(
                true,
            );
        }
    }

    return (
        <div className="space-y-3">
            <div>
                <label
                    htmlFor={id}
                    className="text-sm font-medium text-slate-900"
                >
                    {label}
                </label>

                {description ? (
                    <p className="mt-1 text-sm text-slate-500">
                        {description}
                    </p>
                ) : null}
            </div>

            <div
                className={[
                    "overflow-hidden rounded-xl border bg-white",
                    error
                        ? "border-red-300"
                        : "border-slate-200",
                ].join(" ")}
            >
                <div className="flex min-h-44 items-center justify-center bg-slate-50 p-4">
                    {displayedImageUrl ? (
                        <img
                            src={
                                displayedImageUrl
                            }
                            alt={label}
                            className="max-h-40 max-w-full rounded-lg object-contain"
                        />
                    ) : (
                        <div className="flex flex-col items-center gap-2 text-slate-400">
                            <ImageIcon
                                size={36}
                            />

                            <span className="text-sm">
                                No image
                            </span>
                        </div>
                    )}
                </div>

                <div className="flex flex-wrap items-center gap-2 border-t border-slate-200 p-3">
                    <label
                        htmlFor={id}
                        className={[
                            "inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50",
                            disabled
                                ? "pointer-events-none opacity-50"
                                : "",
                        ].join(" ")}
                    >
                        <Upload size={16} />

                        {displayedImageUrl
                            ? "Replace image"
                            : "Choose image"}
                    </label>

                    <input
                        id={id}
                        data-form-field={
                            fieldName
                        }
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        disabled={disabled}
                        onChange={
                            handleFileChange
                        }
                        className="sr-only"
                    />

                    {file ||
                    (
                        currentImageUrl &&
                        !removeExisting
                    ) ? (
                        <button
                            type="button"
                            disabled={
                                disabled
                            }
                            onClick={
                                handleRemove
                            }
                            className="inline-flex items-center gap-2 rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Trash2
                                size={16}
                            />

                            Remove
                        </button>
                    ) : null}

                    {currentImageUrl &&
                    removeExisting &&
                    !file ? (
                        <button
                            type="button"
                            disabled={
                                disabled
                            }
                            onClick={() =>
                                onRemoveExistingChange(
                                    false,
                                )
                            }
                            className="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <RotateCcw
                                size={16}
                            />

                            Keep existing image
                        </button>
                    ) : null}
                </div>
            </div>

            {file ? (
                <p className="text-xs text-slate-500">
                    Selected: {file.name}
                </p>
            ) : null}

            {currentImageUrl &&
            removeExisting &&
            !file ? (
                <p className="text-sm text-amber-600">
                    The existing image will
                    be removed when you save.
                </p>
            ) : null}

            {error ? (
                <p
                    id={`${id}-error`}
                    className="text-sm text-red-600"
                >
                    {error}
                </p>
            ) : null}
        </div>
    );
}