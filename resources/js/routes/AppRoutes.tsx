import { Navigate, Route, Routes } from "react-router-dom";

import CategoriesPage from "@/features/categories/pages/CategoriesPage";
import CategoryCreatePage from "@/features/categories/pages/CategoryCreatePage";
import CategoryEditPage from "@/features/categories/pages/CategoryEditPage";

import ProductsPage from "@/features/products/pages/ProductsPage";
import ProductCreatePage from "@/features/products/pages/ProductCreatePage";
import ProductEditPage from "@/features/products/pages/ProductEditPage";

import CustomersPage from "@/features/customers/pages/CustomersPage";
import CustomerDetailPage from "@/features/customers/pages/CustomerDetailPage";

import DashboardPage from "@/features/dashboard/pages/DashboardPage";
import LoginPage from "@/features/auth/pages/LoginPage";
import InventoryPage from "@/features/inventory/pages/InventoryPage";
import OrdersPage from "@/features/orders/pages/OrdersPage";

import AdminLayout from "@/layouts/AdminLayout";

import GuestRoute from "./GuestRoute";
import ProtectedRoute from "./ProtectedRoute";

export default function AppRoutes() {
    return (
        <Routes>
            <Route element={<GuestRoute />}>
                <Route path="/login" element={<LoginPage />} />
            </Route>

            <Route element={<ProtectedRoute />}>
                <Route element={<AdminLayout />}>
                    <Route
                        index
                        element={<Navigate to="/dashboard" replace />}
                    />

                    <Route path="/dashboard" element={<DashboardPage />} />

                    <Route path="/categories" element={<CategoriesPage />} />
                    <Route
                        path="/categories/create"
                        element={<CategoryCreatePage />}
                    />
                    <Route
                        path="/categories/:categoryId/edit"
                        element={<CategoryEditPage />}
                    />
                    <Route path="/products" element={<ProductsPage />} />

                    <Route
                        path="/products/create"
                        element={<ProductCreatePage />}
                    />

                    <Route
                        path="/products/:productId/edit"
                        element={<ProductEditPage />}
                    />

                    <Route path="/inventory" element={<InventoryPage />} />

                    <Route path="/customers" element={<CustomersPage />} />

                    <Route
                        path="/customers/:customerId"
                        element={<CustomerDetailPage />}
                    />

                    <Route path="/orders" element={<OrdersPage />} />
                </Route>
            </Route>

            <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
    );
}
