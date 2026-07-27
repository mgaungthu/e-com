import type { ReactNode } from "react";

import { FieldError } from "@/components/forms/FieldError";

type FormFieldProps = {
    label: ReactNode;
    htmlFor?: string;
    required?: boolean;
    helperText?: ReactNode;
    error?: string | null;
    className?: string;
    children: ReactNode;
};

export function FormField({
    label,
    htmlFor,
    required = false,
    helperText,
    error,
    className = "",
    children,
}: FormFieldProps) {
    const errorId = htmlFor ? `${htmlFor}-error` : undefined;

    return (
        <div className={className}>
            <label
                htmlFor={htmlFor}
                className="mb-2 block text-sm font-semibold text-slate-700"
            >
                {label}

                {required ? (
                    <span className="ml-1 text-red-500" aria-hidden="true">
                        *
                    </span>
                ) : null}
            </label>

            {children}

            {error ? (
                <FieldError id={errorId} message={error} />
            ) : helperText ? (
                <p className="mt-1.5 text-xs text-slate-500">
                    {helperText}
                </p>
            ) : null}
        </div>
    );
}
