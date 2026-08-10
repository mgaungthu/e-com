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
import OrderDetailPage from "@/features/orders/pages/OrderDetailPage";
import AccessManagementPage from "@/features/access/pages/AccessManagementPage";
import ReportsPage from "@/features/reports/pages/ReportsPage";
import SettingsPage from "@/features/settings/pages/SettingsPage";
import FeedsPage from "@/features/feeds/pages/FeedsPage";
import FeedCreatePage from "@/features/feeds/pages/FeedCreatePage";
import FeedEditPage from "@/features/feeds/pages/FeedEditPage";

import AdminLayout from "@/layouts/AdminLayout";

import GuestRoute from "./GuestRoute";
import ProtectedRoute from "./ProtectedRoute";
import PermissionRoute from "./PermissionRoute";

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

                    <Route path="/dashboard" element={<PermissionRoute permission="dashboard.view"><DashboardPage /></PermissionRoute>} />

                    <Route path="/categories" element={<CategoriesPage />} />
                    <Route
                        path="/categories/create"
                        element={<CategoryCreatePage />}
                    />
                    <Route
                        path="/categories/:categoryId/edit"
                        element={<CategoryEditPage />}
                    />
                    <Route
                        path="/products"
                        element={
                            <PermissionRoute permission="products.view">
                                <ProductsPage />
                            </PermissionRoute>
                        }
                    />

                    <Route
                        path="/products/create"
                        element={
                            <PermissionRoute permission="products.create">
                                <ProductCreatePage />
                            </PermissionRoute>
                        }
                    />

                    <Route
                        path="/products/:productId/edit"
                        element={
                            <PermissionRoute permission="products.update">
                                <ProductEditPage />
                            </PermissionRoute>
                        }
                    />
                    <Route path="/feeds" element={<PermissionRoute permission="feeds.view"><FeedsPage /></PermissionRoute>} />
                    <Route path="/feeds/create" element={<PermissionRoute permission="feeds.create"><FeedCreatePage /></PermissionRoute>} />
                    <Route path="/feeds/:feedId/edit" element={<PermissionRoute permission="feeds.update"><FeedEditPage /></PermissionRoute>} />

                    <Route
                        path="/inventory"
                        element={
                            <PermissionRoute permission="inventory.view">
                                <InventoryPage />
                            </PermissionRoute>
                        }
                    />

                    <Route path="/customers" element={<CustomersPage />} />

                    <Route
                        path="/customers/:customerId"
                        element={<CustomerDetailPage />}
                    />

                    <Route
                        path="/orders"
                        element={
                            <PermissionRoute permission="orders.view">
                                <OrdersPage />
                            </PermissionRoute>
                        }
                    />
                    <Route
                        path="/orders/:orderId"
                        element={
                            <PermissionRoute permission="orders.view">
                                <OrderDetailPage />
                            </PermissionRoute>
                        }
                    />
                    <Route path="/access" element={<PermissionRoute permission="staff.view"><AccessManagementPage /></PermissionRoute>} />
                    <Route path="/reports" element={<PermissionRoute permission="reports.view"><ReportsPage /></PermissionRoute>} />
                    <Route path="/settings" element={<PermissionRoute permission="settings.manage"><SettingsPage /></PermissionRoute>} />
                </Route>
            </Route>

            <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
    );
}
