import axios from "axios";

const csrfToken = document

    .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')

    ?.getAttribute("content");

export const api = axios.create({
    baseURL: "/",
    withCredentials: true,
    headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
        ...(csrfToken
            ? {
                  "X-CSRF-TOKEN": csrfToken,
              }
            : {}),
    },
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 419) {
            window.location.reload();
        }

        return Promise.reject(error);
    },
);
