import {
    forwardRef,
    type SelectHTMLAttributes,
} from "react";

export type SelectInputProps =
    SelectHTMLAttributes<HTMLSelectElement> & {
        error?: string | null;
    };

export const SelectInput = forwardRef<
    HTMLSelectElement,
    SelectInputProps
>(function SelectInput({ error, className = "", ...props }, ref) {
    return (
        <select
            ref={ref}
            aria-invalid={Boolean(error)}
            className={[
                "w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500",
                error
                    ? "border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-100"
                    : "border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100",
                className,
            ]
                .filter(Boolean)
                .join(" ")}
            {...props}
        />
    );
});
