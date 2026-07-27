import { FieldError } from "@/components/forms/FieldError";

type CheckboxCardProps = {
    name?: string;
    title: string;
    description?: string;
    checked: boolean;
    disabled?: boolean;
    error?: string | null;
    onChange: (checked: boolean) => void;
    className?: string;
    "data-form-field"?: string;
};

export function CheckboxCard({
    name,
    title,
    description,
    checked,
    disabled = false,
    error,
    onChange,
    className = "",
    "data-form-field": dataFormField,
}: CheckboxCardProps) {
    const errorId = name ? `${name}-error` : undefined;

    return (
        <div className={className}>
            <label
                className={[
                    "flex items-start justify-between gap-4 rounded-xl border p-4 transition",
                    disabled
                        ? "cursor-not-allowed opacity-60"
                        : "cursor-pointer",
                    error
                        ? "border-red-400"
                        : checked
                          ? "border-blue-200 bg-blue-50/50"
                          : "border-slate-200 hover:bg-slate-50",
                ].join(" ")}
            >
                <div>
                    <p className="text-sm font-semibold text-slate-900">
                        {title}
                    </p>

                    {description ? (
                        <p className="mt-1 text-xs leading-5 text-slate-500">
                            {description}
                        </p>
                    ) : null}
                </div>

                <input
                    type="checkbox"
                    name={name}
                    data-form-field={dataFormField}
                    checked={checked}
                    disabled={disabled}
                    aria-invalid={Boolean(error)}
                    aria-describedby={error ? errorId : undefined}
                    onChange={(event) => onChange(event.target.checked)}
                    className="mt-0.5 h-5 w-5 shrink-0 rounded border-slate-300 text-blue-600 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50"
                />
            </label>

            <FieldError id={errorId} message={error} />
        </div>
    );
}
