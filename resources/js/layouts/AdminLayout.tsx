import {
    Boxes,
    LayoutDashboard,
    LogOut,
    Package,
    Images,
    Menu,
    ShoppingCart,
    Tags,
    Users,
    ShieldCheck,
    FileBarChart,
    Settings,
    X,
} from "lucide-react";

import { useState } from "react";
import { NavLink, Outlet, useNavigate } from "react-router-dom";

import { useAuthStore } from "@/store/authStore";

const navigationItems = [
    {
        label: "Dashboard",
        path: "/dashboard",
        icon: LayoutDashboard,
        permission: "dashboard.view",
    },
    {
        label: "Categories",
        path: "/categories",
        icon: Tags,
        permission: "categories.view",
    },
    {
        label: "Products",
        path: "/products",
        icon: Package,
        permission: "products.view",
    },
    {
        label: "Feeds",
        path: "/feeds",
        icon: Images,
        permission: "feeds.view",
    },
    {
        label: "Inventory",
        path: "/inventory",
        icon: Boxes,
        permission: "inventory.view",
    },
    {
        label: "Orders",
        path: "/orders",
        icon: ShoppingCart,
        permission: "orders.view",
    },
    {
        label: "Customers",
        path: "/customers",
        icon: Users,
        permission: "customers.view",
    },
    {
        label: "Access Control",
        path: "/access",
        icon: ShieldCheck,
        permission: "staff.view",
    },
    {
        label: "Reports",
        path: "/reports",
        icon: FileBarChart,
        permission: "reports.view",
    },
    {
        label: "Settings",
        path: "/settings",
        icon: Settings,
        permission: "settings.manage",
    },
];

export default function AdminLayout() {
    const [isMobileNavigationOpen, setIsMobileNavigationOpen] = useState(false);
    const navigate = useNavigate();

    const user = useAuthStore((state) => state.user);

    const logout = useAuthStore((state) => state.logout);

    async function handleLogout() {
        await logout();

        navigate("/login", {
            replace: true,
        });
    }

    function closeMobileNavigation() {
        setIsMobileNavigationOpen(false);
    }

    return (
        <div className="min-h-screen bg-slate-100">
            {isMobileNavigationOpen ? (
                <button
                    type="button"
                    aria-label="Close navigation menu"
                    onClick={closeMobileNavigation}
                    className="fixed inset-0 z-30 bg-slate-950/50 lg:hidden"
                />
            ) : null}

            <aside
                className={[
                    "fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-slate-950 text-white transition-transform duration-200 lg:z-20 lg:translate-x-0",
                    isMobileNavigationOpen
                        ? "translate-x-0"
                        : "-translate-x-full",
                ].join(" ")}
            >
                <div className="border-b border-slate-800 px-6 py-5">
                    <div className="flex items-center justify-between">
                        <h1 className="text-xl font-bold">E-commerce</h1>

                        <button
                            type="button"
                            aria-label="Close navigation menu"
                            onClick={closeMobileNavigation}
                            className="rounded-lg p-2 text-slate-300 transition hover:bg-slate-800 hover:text-white lg:hidden"
                        >
                            <X size={18} />
                        </button>
                    </div>

                    <p className="mt-1 text-xs text-slate-400">
                        Admin Dashboard
                    </p>
                </div>

                <nav className="flex-1 space-y-1 overflow-y-auto p-4">
                    {navigationItems
                        .filter((item) =>
                            user?.permissions.includes(item.permission),
                        )
                        .map((item) => {
                            const Icon = item.icon;

                            return (
                                <NavLink
                                    key={item.path}
                                    to={item.path}
                                    onClick={closeMobileNavigation}
                                    className={({ isActive }) =>
                                        [
                                            "flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition",
                                            isActive
                                                ? "bg-blue-600 text-white"
                                                : "text-slate-300 hover:bg-slate-800 hover:text-white",
                                        ].join(" ")
                                    }
                                >
                                    <Icon size={18} />
                                    {item.label}
                                </NavLink>
                            );
                        })}
                </nav>
            </aside>

            <div className="min-h-screen lg:ml-64">
                <header className="flex h-16 items-center justify-between gap-3 border-b bg-white px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            aria-label="Open navigation menu"
                            aria-expanded={isMobileNavigationOpen}
                            onClick={() => setIsMobileNavigationOpen(true)}
                            className="rounded-lg border border-slate-200 p-2 text-slate-700 transition hover:bg-slate-50 lg:hidden"
                        >
                            <Menu size={20} />
                        </button>
                        <p className="text-sm font-medium text-slate-800">
                            Admin Panel
                        </p>
                    </div>

                    <div className="flex shrink-0 items-center gap-2 sm:gap-4">
                        <div className="hidden text-right sm:block">
                            <p className="text-sm font-semibold text-slate-900">
                                {user?.name}
                            </p>

                            <p className="text-xs text-slate-500">
                                {user?.roles.join(", ")}
                            </p>
                        </div>

                        <button
                            type="button"
                            onClick={handleLogout}
                            className="flex items-center gap-2 rounded-lg border px-2.5 py-2 text-sm text-slate-700 transition hover:bg-slate-50 sm:px-3"
                        >
                            <LogOut size={16} />
                            <span className="hidden sm:inline">Logout</span>
                        </button>
                    </div>
                </header>

                <main className="p-4 sm:p-6 lg:p-8">
                    <Outlet />
                </main>
            </div>
        </div>
    );
}
