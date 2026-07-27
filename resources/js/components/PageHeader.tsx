import type { ReactNode } from "react";

import { ArrowLeft } from "lucide-react";

type PageHeaderProps = {
    title: ReactNode;
    description?: ReactNode;
    backLabel?: string;
    onBack?: () => void;
    className?: string;
};

export default function PageHeader({
    title,
    description,
    backLabel,
    onBack,
    className = "",
}: PageHeaderProps) {
    return (
        <div className={className}>
            {backLabel && onBack ? (
                <button
                    type="button"
                    onClick={onBack}
                    className="mb-4 inline-flex items-center gap-2 text-sm font-medium text-slate-600 transition hover:text-slate-900"
                >
                    <ArrowLeft size={17} />
                    {backLabel}
                </button>
            ) : null}

            <h1 className="text-2xl font-bold text-slate-900">
                {title}
            </h1>

            {description ? (
                <p className="mt-1 text-sm text-slate-500">
                    {description}
                </p>
            ) : null}
        </div>
    );
}
