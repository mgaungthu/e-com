import { Navigate, Outlet, useLocation } from "react-router-dom";

import FullPageLoader from "@/components/FullPageLoader";
import { useAuthStore } from "@/store/authStore";

export default function ProtectedRoute() {
    const location = useLocation();

    const isAuthenticated = useAuthStore((state) => state.isAuthenticated);

    const isInitializing = useAuthStore((state) => state.isInitializing);

    if (isInitializing) {
        return <FullPageLoader />;
    }

    if (!isAuthenticated) {
        return (
            <Navigate
                to="/login"
                replace
                state={{
                    from: location.pathname,
                }}
            />
        );
    }

    return <Outlet />;
}
