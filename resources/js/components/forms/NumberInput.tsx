import {
    forwardRef,
    type InputHTMLAttributes,
    type ReactNode,
} from "react";

import { TextInput } from "@/components/forms/TextInput";

type NumberInputProps = Omit<
    InputHTMLAttributes<HTMLInputElement>,
    "type"
> & {
    error?: string | null;
    prefix?: ReactNode;
    suffix?: ReactNode;
};

export const NumberInput = forwardRef<
    HTMLInputElement,
    NumberInputProps
>(function NumberInput(
    {
        error,
        prefix,
        suffix,
        className = "",
        ...props
    },
    ref,
) {
    const input = (
        <TextInput
            ref={ref}
            type="number"
            error={error}
            className={[
                prefix ? "pl-8" : "",
                suffix ? "pr-12" : "",
                className,
            ]
                .filter(Boolean)
                .join(" ")}
            {...props}
        />
    );

    if (!prefix && !suffix) {
        return input;
    }

    return (
        <div className="relative">
            {prefix ? (
                <span className="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-medium text-slate-500">
                    {prefix}
                </span>
            ) : null}

            {input}

            {suffix ? (
                <span className="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-sm font-medium text-slate-500">
                    {suffix}
                </span>
            ) : null}
        </div>
    );
});
