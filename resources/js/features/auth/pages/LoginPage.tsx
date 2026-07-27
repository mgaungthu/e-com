import { useState, type FormEvent } from "react";

import axios from "axios";
import {
    Navigate,
    useLocation,
    useNavigate,
} from "react-router-dom";

import { useAuthStore } from "@/store/authStore";

type LocationState = {
    from?: string;
};

type ValidationErrors = Record<string, string[]>;

type ErrorResponse = {
    message?: string;
    errors?: ValidationErrors;
};

export default function LoginPage() {
    const navigate = useNavigate();
    const location = useLocation();

    const login = useAuthStore((state) => state.login);

    const isLoggingIn = useAuthStore(
        (state) => state.isLoggingIn,
    );

    const isAuthenticated = useAuthStore(
        (state) => state.isAuthenticated,
    );

    const [email, setEmail] = useState(
        "admin@example.com",
    );

    const [password, setPassword] = useState(
        "password123",
    );

    const [remember, setRemember] = useState(false);

    const [formError, setFormError] = useState<
        string | null
    >(null);

    const [fieldErrors, setFieldErrors] =
        useState<ValidationErrors>({});

    const state =
        location.state as LocationState | null;

    const redirectPath =
        state?.from ?? "/dashboard";

    if (isAuthenticated) {
        return (
            <Navigate
                to="/dashboard"
                replace
            />
        );
    }

    function clearFieldError(field: string) {
        setFieldErrors((currentErrors) => {
            if (!currentErrors[field]) {
                return currentErrors;
            }

            const nextErrors = {
                ...currentErrors,
            };

            delete nextErrors[field];

            return nextErrors;
        });
    }

    async function handleSubmit(
        event: FormEvent<HTMLFormElement>,
    ) {
        event.preventDefault();

        setFormError(null);
        setFieldErrors({});

        try {
            await login({
                email: email.trim(),
                password,
                remember,
            });

            navigate(redirectPath, {
                replace: true,
            });
        } catch (error) {
            if (
                !axios.isAxiosError<ErrorResponse>(
                    error,
                )
            ) {
                setFormError(
                    "An unexpected error occurred. Please try again.",
                );

                return;
            }

            if (!error.response) {
                setFormError(
                    "Unable to connect to the server. Please check your connection and try again.",
                );

                return;
            }

            const status = error.response.status;
            const responseData =
                error.response.data ?? {};

            if (status === 422) {
                const validationErrors =
                    responseData.errors ?? {};

                setFieldErrors(validationErrors);

                setFormError(
                    validationErrors.credentials?.[0] ??
                        responseData.message ??
                        "Please check your login information.",
                );

                return;
            }

            if (status === 403) {
                setFormError(
                    responseData.message ??
                        "You are not allowed to access the admin dashboard.",
                );

                return;
            }

            if (status === 419) {
                setFormError(
                    "Your session has expired. Please refresh the page and try again.",
                );

                return;
            }

            if (status === 429) {
                setFormError(
                    "Too many login attempts. Please wait a moment and try again.",
                );

                return;
            }

            if (status >= 500) {
                setFormError(
                    "The server encountered an error. Please try again later.",
                );

                return;
            }

            setFormError(
                responseData.message ??
                    "Unable to sign in. Please try again.",
            );
        }
    }

    return (
        <div className="flex min-h-screen bg-slate-100">
            <div className="hidden w-1/2 flex-col justify-between bg-slate-950 p-12 text-white lg:flex">
                <div>
                    <h1 className="text-3xl font-bold">
                        E-commerce
                    </h1>

                    <p className="mt-2 text-slate-400">
                        Admin Management System
                    </p>
                </div>

                <div>
                    <h2 className="max-w-lg text-4xl font-bold leading-tight">
                        Manage products, inventory,
                        customers and orders in one place.
                    </h2>

                    <p className="mt-5 max-w-lg text-slate-400">
                        Sign in to access the admin dashboard.
                    </p>
                </div>

                <p className="text-sm text-slate-500">
                    © 2026 E-commerce
                </p>
            </div>

            <div className="flex w-full items-center justify-center p-6 lg:w-1/2">
                <div className="w-full max-w-md rounded-2xl bg-white p-8 shadow-sm">
                    <div className="mb-8">
                        <h2 className="text-2xl font-bold text-slate-900">
                            Welcome back
                        </h2>

                        <p className="mt-2 text-sm text-slate-500">
                            Enter your admin account
                            credentials.
                        </p>
                    </div>

                    {formError ? (
                        <div
                            role="alert"
                            aria-live="polite"
                            className="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                        >
                            {formError}
                        </div>
                    ) : null}

                    <form
                        onSubmit={handleSubmit}
                        className="space-y-5"
                        noValidate
                    >
                        <div>
                            <label
                                htmlFor="email"
                                className="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Email address
                            </label>

                            <input
                                id="email"
                                type="email"
                                value={email}
                                onChange={(event) => {
                                    setEmail(
                                        event.target.value,
                                    );

                                    clearFieldError(
                                        "email",
                                    );

                                    if (formError) {
                                        setFormError(null);
                                    }
                                }}
                                autoComplete="email"
                                aria-invalid={
                                    Boolean(
                                        fieldErrors
                                            .email?.[0],
                                    )
                                }
                                aria-describedby={
                                    fieldErrors.email?.[0]
                                        ? "email-error"
                                        : undefined
                                }
                                className={[
                                    "w-full rounded-lg border px-4 py-3 outline-none transition",
                                    fieldErrors.email?.[0]
                                        ? "border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-100"
                                        : "border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100",
                                ].join(" ")}
                                placeholder="admin@example.com"
                            />

                            {fieldErrors.email?.[0] ? (
                                <p
                                    id="email-error"
                                    className="mt-2 text-sm text-red-600"
                                >
                                    {
                                        fieldErrors
                                            .email[0]
                                    }
                                </p>
                            ) : null}
                        </div>

                        <div>
                            <label
                                htmlFor="password"
                                className="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Password
                            </label>

                            <input
                                id="password"
                                type="password"
                                value={password}
                                onChange={(event) => {
                                    setPassword(
                                        event.target.value,
                                    );

                                    clearFieldError(
                                        "password",
                                    );

                                    if (formError) {
                                        setFormError(null);
                                    }
                                }}
                                autoComplete="current-password"
                                aria-invalid={
                                    Boolean(
                                        fieldErrors
                                            .password?.[0],
                                    )
                                }
                                aria-describedby={
                                    fieldErrors.password?.[0]
                                        ? "password-error"
                                        : undefined
                                }
                                className={[
                                    "w-full rounded-lg border px-4 py-3 outline-none transition",
                                    fieldErrors.password?.[0]
                                        ? "border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-100"
                                        : "border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100",
                                ].join(" ")}
                                placeholder="Enter password"
                            />

                            {fieldErrors.password?.[0] ? (
                                <p
                                    id="password-error"
                                    className="mt-2 text-sm text-red-600"
                                >
                                    {
                                        fieldErrors
                                            .password[0]
                                    }
                                </p>
                            ) : null}
                        </div>

                        <label className="flex cursor-pointer items-center gap-3">
                            <input
                                type="checkbox"
                                checked={remember}
                                onChange={(event) =>
                                    setRemember(
                                        event.target.checked,
                                    )
                                }
                                className="h-4 w-4 rounded border-slate-300"
                            />

                            <span className="text-sm text-slate-600">
                                Remember me
                            </span>
                        </label>

                        <button
                            type="submit"
                            disabled={isLoggingIn}
                            className="w-full rounded-lg bg-blue-600 px-4 py-3 font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {isLoggingIn
                                ? "Signing in..."
                                : "Sign in"}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    );
}