import type { ReactNode } from "react";

import { Check } from "lucide-react";

type FormActionsProps = {
    submitLabel: string;
    submittingLabel?: string;
    isSubmitting: boolean;
    submitDisabled?: boolean;
    disabled?: boolean;
    onCancel?: () => void;
    cancelLabel?: string;
    submitIcon?: ReactNode;
    className?: string;
};

export function FormActions({
    submitLabel,
    submittingLabel = "Saving...",
    isSubmitting,
    submitDisabled = false,
    disabled = false,
    onCancel,
    cancelLabel = "Cancel",
    submitIcon = <Check size={18} />,
    className = "",
}: FormActionsProps) {
    const controlsDisabled = disabled || isSubmitting;

    return (
        <div
            className={[
                "flex flex-col-reverse gap-3 rounded-xl border border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-end",
                className,
            ]
                .filter(Boolean)
                .join(" ")}
        >
            {onCancel ? (
                <button
                    type="button"
                    onClick={onCancel}
                    disabled={controlsDisabled}
                    className="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {cancelLabel}
                </button>
            ) : null}

            <button
                type="submit"
                disabled={controlsDisabled || submitDisabled}
                className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
                {submitIcon}
                {isSubmitting ? submittingLabel : submitLabel}
            </button>
        </div>
    );
}
