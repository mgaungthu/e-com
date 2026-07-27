import { Navigate, Outlet } from "react-router-dom";

import FullPageLoader from "@/components/FullPageLoader";
import { useAuthStore } from "@/store/authStore";

export default function GuestRoute() {
    const isAuthenticated = useAuthStore((state) => state.isAuthenticated);

    const isInitializing = useAuthStore((state) => state.isInitializing);

    if (isInitializing) {
        return <FullPageLoader />;
    }

    if (isAuthenticated) {
        return <Navigate to="/dashboard" replace />;
    }

    return <Outlet />;
}
