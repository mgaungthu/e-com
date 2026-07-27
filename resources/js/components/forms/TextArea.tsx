import {
    forwardRef,
    type TextareaHTMLAttributes,
} from "react";

export type TextAreaProps =
    TextareaHTMLAttributes<HTMLTextAreaElement> & {
        error?: string | null;
    };

export const TextArea = forwardRef<
    HTMLTextAreaElement,
    TextAreaProps
>(function TextArea({ error, className = "", ...props }, ref) {
    return (
        <textarea
            ref={ref}
            aria-invalid={Boolean(error)}
            className={[
                "w-full resize-y rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500",
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
