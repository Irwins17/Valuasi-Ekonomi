export function toNumber(value, fallback = 0) {
    if (value === null || value === undefined || value === '') return fallback;
    const n = Number(value);
    return Number.isNaN(n) ? fallback : n;
}

export function formatRupiah(value, decimals = 0) {
    return toNumber(value).toLocaleString('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });
}

export function formatTriliun(value, decimals = 1) {
    return (toNumber(value) / 1e9).toFixed(decimals);
}
