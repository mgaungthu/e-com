import React from "react";
import ReactDOM from "react-dom/client";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter } from "react-router-dom";

import RootApp from "@/RootApp";

const rootElement = document.getElementById("app");

if (!rootElement) {
    throw new Error("React root element not found.");
}

const queryClient = new QueryClient({
    defaultOptions: {
        queries: {
            staleTime: 30_000,
            retry: 1,
            refetchOnWindowFocus: false,
        },
    },
});

ReactDOM.createRoot(rootElement).render(
    <React.StrictMode>
        <QueryClientProvider client={queryClient}>
            <BrowserRouter>
                <RootApp />
            </BrowserRouter>
        </QueryClientProvider>
    </React.StrictMode>,
);