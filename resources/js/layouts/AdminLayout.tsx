import {
    Boxes,
    LayoutDashboard,
    LogOut,
    Package,
    ShoppingCart,
    Tags,
    Users,
} from "lucide-react";

import { NavLink, Outlet, useNavigate } from "react-router-dom";

import { useAuthStore } from "@/store/authStore";

const navigationItems = [
    {
        label: "Dashboard",
        path: "/dashboard",
        icon: LayoutDashboard,
    },
    {
        label: "Categories",
        path: "/categories",
        icon: Tags,
    },
    {
        label: "Products",
        path: "/products",
        icon: Package,
    },
    {
        label: "Inventory",
        path: "/inventory",
        icon: Boxes,
    },
    {
        label: "Orders",
        path: "/orders",
        icon: ShoppingCart,
    },
    {
        label: "Customers",
        path: "/customers",
        icon: Users,
    },
];

export default function AdminLayout() {
    const navigate = useNavigate();

    const user = useAuthStore((state) => state.user);

    const logout = useAuthStore((state) => state.logout);

    async function handleLogout() {
        await logout();

        navigate("/login", {
            replace: true,
        });
    }

    return (
        <div className="min-h-screen bg-slate-100">
            <aside className="fixed inset-y-0 left-0 z-20 w-64 bg-slate-950 text-white">
                <div className="border-b border-slate-800 px-6 py-5">
                    <h1 className="text-xl font-bold">E-commerce</h1>

                    <p className="mt-1 text-xs text-slate-400">
                        Admin Dashboard
                    </p>
                </div>

                <nav className="space-y-1 p-4">
                    {navigationItems.map((item) => {
                        const Icon = item.icon;

                        return (
                            <NavLink
                                key={item.path}
                                to={item.path}
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

            <div className="ml-64 min-h-screen">
                <header className="flex h-16 items-center justify-between border-b bg-white px-8">
                    <div>
                        <p className="text-sm font-medium text-slate-800">
                            Admin Panel
                        </p>
                    </div>

                    <div className="flex items-center gap-4">
                        <div className="text-right">
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
                            className="flex items-center gap-2 rounded-lg border px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-50"
                        >
                            <LogOut size={16} />
                            Logout
                        </button>
                    </div>
                </header>

                <main className="p-8">
                    <Outlet />
                </main>
            </div>
        </div>
    );
}
