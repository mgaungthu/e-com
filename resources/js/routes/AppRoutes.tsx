import { Navigate, Route, Routes } from "react-router-dom";

import AccessManagementPage from "@/features/access/pages/AccessManagementPage";

import LoginPage from "@/features/auth/pages/LoginPage";

import CategoriesPage from "@/features/categories/pages/CategoriesPage";
import CategoryCreatePage from "@/features/categories/pages/CategoryCreatePage";
import CategoryEditPage from "@/features/categories/pages/CategoryEditPage";

import LocationsPage from "@/features/locations/pages/LocationsPage";
import LocationCreatePage from "@/features/locations/pages/LocationCreatePage";
import LocationEditPage from "@/features/locations/pages/LocationEditPage";

import CustomersPage from "@/features/customers/pages/CustomersPage";
import CustomerDetailPage from "@/features/customers/pages/CustomerDetailPage";

import DashboardPage from "@/features/dashboard/pages/DashboardPage";

import FeedsPage from "@/features/feeds/pages/FeedsPage";
import FeedCreatePage from "@/features/feeds/pages/FeedCreatePage";
import FeedEditPage from "@/features/feeds/pages/FeedEditPage";

import HomeBannersPage from "@/features/home-banners/pages/HomeBannersPage";
import HomeBannerCreatePage from "@/features/home-banners/pages/HomeBannerCreatePage";
import HomeBannerEditPage from "@/features/home-banners/pages/HomeBannerEditPage";

import InventoryPage from "@/features/inventory/pages/InventoryPage";

import OrdersPage from "@/features/orders/pages/OrdersPage";
import OrderDetailPage from "@/features/orders/pages/OrderDetailPage";

import PaymentMethodsPage from "@/features/payment-methods/pages/PaymentMethodsPage";
import PaymentMethodCreatePage from "@/features/payment-methods/pages/PaymentMethodCreatePage";
import PaymentMethodEditPage from "@/features/payment-methods/pages/PaymentMethodEditPage";

import ProductsPage from "@/features/products/pages/ProductsPage";
import ProductCreatePage from "@/features/products/pages/ProductCreatePage";
import ProductEditPage from "@/features/products/pages/ProductEditPage";

import ReportsPage from "@/features/reports/pages/ReportsPage";

import NotificationsPage from "@/features/notifications/pages/NotificationsPage";

import SettingsPage from "@/features/settings/pages/SettingsPage";

import AdminLayout from "@/layouts/AdminLayout";

import GuestRoute from "./GuestRoute";
import PermissionRoute from "./PermissionRoute";
import ProtectedRoute from "./ProtectedRoute";

export default function AppRoutes() {
    return (
        <Routes>
            {/*
            |--------------------------------------------------------------------------
            | Guest Routes
            |--------------------------------------------------------------------------
            */}

            <Route element={<GuestRoute />}>
                <Route path="/login" element={<LoginPage />} />
            </Route>

            {/*
            |--------------------------------------------------------------------------
            | Protected Admin Routes
            |--------------------------------------------------------------------------
            */}

            <Route element={<ProtectedRoute />}>
                <Route element={<AdminLayout />}>
                    <Route
                        index
                        element={<Navigate to="/dashboard" replace />}
                    />

                    {/*
                    |--------------------------------------------------------------------------
                    | Dashboard
                    |--------------------------------------------------------------------------
                    */}

                    <Route
                        path="/dashboard"
                        element={
                            <PermissionRoute permission="dashboard.view">
                                <DashboardPage />
                            </PermissionRoute>
                        }
                    />

                    {/*
                    |--------------------------------------------------------------------------
                    | Home Banners
                    |--------------------------------------------------------------------------
                    */}

                    <Route
                        path="/home-banners"
                        element={
                            <PermissionRoute permission="home_banners.view">
                                <HomeBannersPage />
                            </PermissionRoute>
                        }
                    />

                    <Route
                        path="/home-banners/create"
                        element={
                            <PermissionRoute permission="home_banners.create">
                                <HomeBannerCreatePage />
                            </PermissionRoute>
                        }
                    />

                    <Route
                        path="/home-banners/:bannerId/edit"
                        element={
                            <PermissionRoute permission="home_banners.update">
                                <HomeBannerEditPage />
                            </PermissionRoute>
                        }
                    />

                    {/*
                    |--------------------------------------------------------------------------
                    | Categories
                    |--------------------------------------------------------------------------
                    */}

                    <Route path="/categories" element={<CategoriesPage />} />

                    <Route
                        path="/categories/create"
                        element={<CategoryCreatePage />}
                    />

                    <Route
                        path="/categories/:categoryId/edit"
                        element={<CategoryEditPage />}
                    />

                    {/*
                    |--------------------------------------------------------------------------
                    | Locations
                    |--------------------------------------------------------------------------
                    */}

                    <Route
                        path="/locations"
                        element={
                            <PermissionRoute permission="locations.view">
                                <LocationsPage />
                            </PermissionRoute>
                        }
                    />

                    <Route
                        path="/locations/create"
                        element={
                            <PermissionRoute permission="locations.create">
                                <LocationCreatePage />
                            </PermissionRoute>
                        }
                    />

                    <Route
                        path="/locations/:locationId/edit"
                        element={
                            <PermissionRoute permission="locations.update">
                                <LocationEditPage />
                            </PermissionRoute>
                        }
                    />

                    {/*
                    |--------------------------------------------------------------------------
                    | Products
                    |--------------------------------------------------------------------------
                    */}

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

                    {/*
                    |--------------------------------------------------------------------------
                    | Payment Methods
                    |--------------------------------------------------------------------------
                    */}

                    <Route
                        path="/payment-methods"
                        element={
                            <PermissionRoute permission="payment_methods.view">
                                <PaymentMethodsPage />
                            </PermissionRoute>
                        }
                    />

                    <Route
                        path="/payment-methods/create"
                        element={
                            <PermissionRoute permission="payment_methods.create">
                                <PaymentMethodCreatePage />
                            </PermissionRoute>
                        }
                    />

                    <Route
                        path="/payment-methods/:paymentMethodId/edit"
                        element={
                            <PermissionRoute permission="payment_methods.update">
                                <PaymentMethodEditPage />
                            </PermissionRoute>
                        }
                    />

                    {/*
                    |--------------------------------------------------------------------------
                    | Feeds
                    |--------------------------------------------------------------------------
                    */}

                    <Route
                        path="/feeds"
                        element={
                            <PermissionRoute permission="feeds.view">
                                <FeedsPage />
                            </PermissionRoute>
                        }
                    />

                    <Route
                        path="/feeds/create"
                        element={
                            <PermissionRoute permission="feeds.create">
                                <FeedCreatePage />
                            </PermissionRoute>
                        }
                    />

                    <Route
                        path="/feeds/:feedId/edit"
                        element={
                            <PermissionRoute permission="feeds.update">
                                <FeedEditPage />
                            </PermissionRoute>
                        }
                    />

                    {/*
                    |--------------------------------------------------------------------------
                    | Inventory
                    |--------------------------------------------------------------------------
                    */}

                    <Route
                        path="/inventory"
                        element={
                            <PermissionRoute permission="inventory.view">
                                <InventoryPage />
                            </PermissionRoute>
                        }
                    />

                    {/*
                    |--------------------------------------------------------------------------
                    | Customers
                    |--------------------------------------------------------------------------
                    */}

                    <Route path="/customers" element={<CustomersPage />} />

                    <Route
                        path="/customers/:customerId"
                        element={<CustomerDetailPage />}
                    />

                    {/*
                    |--------------------------------------------------------------------------
                    | Orders
                    |--------------------------------------------------------------------------
                    */}

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

                    {/*
                    |--------------------------------------------------------------------------
                    | Access Management
                    |--------------------------------------------------------------------------
                    */}

                    <Route
                        path="/access"
                        element={
                            <PermissionRoute permission="staff.view">
                                <AccessManagementPage />
                            </PermissionRoute>
                        }
                    />

                    {/*
                    |--------------------------------------------------------------------------
                    | Reports
                    |--------------------------------------------------------------------------
                    */}

                    <Route
                        path="/reports"
                        element={
                            <PermissionRoute permission="reports.view">
                                <ReportsPage />
                            </PermissionRoute>
                        }
                    />

                    {/*
                    |--------------------------------------------------------------------------
                    | Notifications
                    |--------------------------------------------------------------------------
                    */}

                    <Route
                        path="/notifications"
                        element={
                            <PermissionRoute permission="notifications.view">
                                <NotificationsPage />
                            </PermissionRoute>
                        }
                    />

                    {/*
                    |--------------------------------------------------------------------------
                    | Settings
                    |--------------------------------------------------------------------------
                    */}

                    <Route
                        path="/settings"
                        element={
                            <PermissionRoute permission="settings.manage">
                                <SettingsPage />
                            </PermissionRoute>
                        }
                    />
                </Route>
            </Route>

            {/*
            |--------------------------------------------------------------------------
            | Fallback
            |--------------------------------------------------------------------------
            */}

            <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
    );
}
