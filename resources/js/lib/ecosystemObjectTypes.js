// Mirrors App\Support\EcosystemObjectTypes — keep both in sync.
export const ECOSYSTEM_OBJECT_TYPES = [
    { value: 'mangrove', label: 'Mangrove' },
    { value: 'terumbu_karang', label: 'Terumbu Karang' },
    { value: 'lamun', label: 'Lamun' },
    { value: 'perikanan_tangkap', label: 'Perikanan Tangkap' },
    { value: 'perikanan_budidaya', label: 'Perikanan Budidaya' },
    { value: 'kawasan_konservasi_laut', label: 'Kawasan Konservasi Laut' },
];

export function ecosystemObjectTypeLabel(value) {
    return ECOSYSTEM_OBJECT_TYPES.find((t) => t.value === value)?.label ?? value;
}
