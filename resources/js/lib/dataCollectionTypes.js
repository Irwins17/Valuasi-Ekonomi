// Mirrors App\Support\DataCollectionTypes — keep both in sync.
export const DATA_COLLECTION_TYPES = [
    { value: 'primer', label: 'Data Primer' },
    { value: 'sekunder', label: 'Data Sekunder' },
];

export const DATA_COLLECTION_METHODS = {
    primer: [
        { value: 'survei_rumah_tangga', label: 'Survei Rumah Tangga' },
        { value: 'wawancara_stakeholder', label: 'Wawancara Stakeholder' },
        { value: 'focus_group_discussion', label: 'Focus Group Discussion (FGD)' },
    ],
    sekunder: [
        { value: 'statistik_resmi', label: 'Statistik Resmi' },
        { value: 'citra_satelit', label: 'Citra Satelit' },
        { value: 'data_ekologi', label: 'Data Ekologi' },
        { value: 'literatur_terdahulu', label: 'Literatur Terdahulu' },
    ],
};

export function collectionMethodsFor(type) {
    return DATA_COLLECTION_METHODS[type] || [];
}
