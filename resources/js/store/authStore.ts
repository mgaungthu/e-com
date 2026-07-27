import { create } from "zustand";

import {
    getAuthenticatedUser,
    login as loginRequest,
    logout as logoutRequest,
} from "../api/auth.api";

import type { AdminUser, LoginPayload } from "../types/auth.types";

type AuthState = {
    user: AdminUser | null;
    isAuthenticated: boolean;
    isInitializing: boolean;
    isLoggingIn: boolean;

    initializeAuth: () => Promise<void>;
    login: (payload: LoginPayload) => Promise<void>;
    logout: () => Promise<void>;
    clearAuth: () => void;
};

export const useAuthStore = create<AuthState>((set) => ({
    user: null,
    isAuthenticated: false,
    isInitializing: true,
    isLoggingIn: false,

    initializeAuth: async () => {
        set({
            isInitializing: true,
        });

        try {
            const response = await getAuthenticatedUser();

            set({
                user: response.data.user,
                isAuthenticated: true,
            });
        } catch {
            set({
                user: null,
                isAuthenticated: false,
            });
        } finally {
            set({
                isInitializing: false,
            });
        }
    },

    login: async (payload) => {
        set({
            isLoggingIn: true,
        });

        try {
            const response = await loginRequest(payload);

            set({
                user: response.data.user,

                isAuthenticated: true,
            });
        } catch (error) {
            set({
                user: null,

                isAuthenticated: false,
            });

            // Original Axios error ကို မပြောင်းဘဲ ပြန်ပစ်ရမယ်

            throw error;
        } finally {
            set({
                isLoggingIn: false,
            });
        }
    },

    logout: async () => {
        try {
            await logoutRequest();
        } finally {
            set({
                user: null,
                isAuthenticated: false,
            });
        }
    },

    clearAuth: () => {
        set({
            user: null,
            isAuthenticated: false,
        });
    },
}));
