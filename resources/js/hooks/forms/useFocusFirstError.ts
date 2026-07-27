import { useEffect } from "react";

type ValidationErrors = Record<
    string,
    readonly string[] | string | null | undefined
>;

type UseFocusFirstErrorOptions = {
    errors: ValidationErrors;
    fieldOrder: readonly string[];
};

function hasError(value: ValidationErrors[string]): boolean {
    return Array.isArray(value)
        ? value.length > 0
        : typeof value === "string" && value.length > 0;
}

export function useFocusFirstError({
    errors,
    fieldOrder,
}: UseFocusFirstErrorOptions) {
    useEffect(() => {
        const errorFields = Object.keys(errors).filter((field) =>
            hasError(errors[field]),
        );

        if (errorFields.length === 0) {
            return;
        }

        const firstField =
            fieldOrder.find((field) => hasError(errors[field])) ??
            errorFields[0];

        let focusTimeout: number | undefined;
        const animationFrame = window.requestAnimationFrame(() => {
            const candidates =
                document.querySelectorAll<HTMLElement>(
                    "[data-form-field]",
                );
            const fieldElement = Array.from(candidates).find(
                (candidate) =>
                    candidate.dataset.formField === firstField,
            );

            if (!fieldElement) {
                return;
            }

            fieldElement.scrollIntoView({
                behavior: "smooth",
                block: "center",
                inline: "nearest",
            });

            focusTimeout = window.setTimeout(() => {
                const focusTarget =
                    fieldElement.matches(
                        "input:not([type='hidden']), select, textarea, button, [tabindex]",
                    )
                        ? fieldElement
                        : fieldElement.querySelector<HTMLElement>(
                              "input:not([type='hidden']), select, textarea, button, [tabindex]",
                          );

                focusTarget?.focus({ preventScroll: true });
            }, 400);
        });

        return () => {
            window.cancelAnimationFrame(animationFrame);

            if (focusTimeout !== undefined) {
                window.clearTimeout(focusTimeout);
            }
        };
    }, [errors, fieldOrder]);
}
