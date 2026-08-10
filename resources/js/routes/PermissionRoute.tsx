import type { ReactNode } from "react";

import { Navigate } from "react-router-dom";

import { useAuthStore } from "@/store/authStore";

type PermissionRouteProps = {
    permission: string;
    children: ReactNode;
};

export default function PermissionRoute({
    permission,
    children,
}: PermissionRouteProps) {
    const hasPermission = useAuthStore((state) =>
        state.user?.permissions.includes(permission),
    );

    return hasPermission ? children : <Navigate to="/dashboard" replace />;
}
