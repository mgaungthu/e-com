type FieldErrorProps = {
    id?: string;
    message?: string | null;
    className?: string;
};

export function FieldError({
    id,
    message,
    className = "",
}: FieldErrorProps) {
    if (!message) {
        return null;
    }

    return (
        <p
            id={id}
            role="alert"
            className={[
                "mt-1.5 text-xs font-medium text-red-600",
                className,
            ]
                .filter(Boolean)
                .join(" ")}
        >
            {message}
        </p>
    );
}
