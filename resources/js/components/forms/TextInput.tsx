import {
    forwardRef,
    type InputHTMLAttributes,
} from "react";

export type TextInputProps =
    InputHTMLAttributes<HTMLInputElement> & {
        error?: string | null;
    };

export const TextInput = forwardRef<
    HTMLInputElement,
    TextInputProps
>(function TextInput(
    { error, className = "", type = "text", ...props },
    ref,
) {
    return (
        <input
            ref={ref}
            type={type}
            aria-invalid={Boolean(error)}
            className={[
                "w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500",
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
