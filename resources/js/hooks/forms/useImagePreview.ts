import {
    useCallback,
    useEffect,
    useRef,
    useState,
} from "react";

export function useImagePreview(initialUrl: string | null) {
    const [previewUrl, setPreviewUrl] =
        useState<string | null>(initialUrl);
    const blobUrlRef = useRef<string | null>(null);

    const revokeBlobUrl = useCallback(() => {
        if (blobUrlRef.current) {
            URL.revokeObjectURL(blobUrlRef.current);
            blobUrlRef.current = null;
        }
    }, []);

    const resetPreview = useCallback(
        (url: string | null = initialUrl) => {
            revokeBlobUrl();
            setPreviewUrl(url);
        },
        [initialUrl, revokeBlobUrl],
    );

    const setFilePreview = useCallback(
        (file: File | null) => {
            if (!file) {
                resetPreview();
                return;
            }

            revokeBlobUrl();
            const nextUrl = URL.createObjectURL(file);
            blobUrlRef.current = nextUrl;
            setPreviewUrl(nextUrl);
        },
        [resetPreview, revokeBlobUrl],
    );

    const removePreview = useCallback(() => {
        revokeBlobUrl();
        setPreviewUrl(null);
    }, [revokeBlobUrl]);

    useEffect(() => {
        resetPreview(initialUrl);
    }, [initialUrl, resetPreview]);

    useEffect(
        () => () => {
            revokeBlobUrl();
        },
        [revokeBlobUrl],
    );

    return {
        previewUrl,
        setFilePreview,
        removePreview,
        resetPreview,
    };
}
