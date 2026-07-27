import type { ReactNode } from "react";

type FormSectionProps = {
    title: ReactNode;
    description?: ReactNode;
    icon?: ReactNode;
    children: ReactNode;
    className?: string;
    contentClassName?: string;
    headerClassName?: string;
};

export function FormSection({
    title,
    description,
    icon,
    children,
    className = "",
    contentClassName = "p-5",
    headerClassName = "border-b border-slate-200 px-5 py-4",
}: FormSectionProps) {
    return (
        <section
            className={[
                "overflow-hidden rounded-xl border border-slate-200 bg-white",
                className,
            ]
                .filter(Boolean)
                .join(" ")}
        >
            <div className={headerClassName}>
                <div className={icon ? "flex items-center gap-3" : undefined}>
                    {icon ? (
                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                            {icon}
                        </div>
                    ) : null}

                    <div>
                        <h2 className="font-semibold text-slate-900">
                            {title}
                        </h2>

                        {description ? (
                            <p className="mt-1 text-sm text-slate-500">
                                {description}
                            </p>
                        ) : null}
                    </div>
                </div>
            </div>

            <div className={contentClassName}>{children}</div>
        </section>
    );
}
