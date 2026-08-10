export function formatMmk(
    value: number | string | null | undefined,
): string {
    const amount = Number(value ?? 0);

    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "MMK",
        currencyDisplay: "code",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number.isFinite(amount) ? amount : 0);
}
