<?php

namespace App\Support;

/**
 * Data Primer vs Data Sekunder classification (flowchart Langkah 5 —
 * "Pengumpulan Data"), shared by every module's data-entry form.
 * Mirrored in resources/js/lib/dataCollectionTypes.js.
 */
class DataCollectionTypes
{
    public const TYPES = [
        'primer' => 'Data Primer',
        'sekunder' => 'Data Sekunder',
    ];

    /** Collection methods, grouped by data type. */
    public const METHODS = [
        'primer' => [
            'survei_rumah_tangga' => 'Survei Rumah Tangga',
            'wawancara_stakeholder' => 'Wawancara Stakeholder',
            'focus_group_discussion' => 'Focus Group Discussion (FGD)',
        ],
        'sekunder' => [
            'statistik_resmi' => 'Statistik Resmi',
            'citra_satelit' => 'Citra Satelit',
            'data_ekologi' => 'Data Ekologi',
            'literatur_terdahulu' => 'Literatur Terdahulu',
        ],
    ];

    /** Flat list of every method code, for validation. */
    public static function allMethodCodes(): array
    {
        return array_keys(array_merge(...array_values(self::METHODS)));
    }

    /** Method codes valid for one data type ('primer' or 'sekunder'). */
    public static function methodCodesFor(string $type): array
    {
        return array_keys(self::METHODS[$type] ?? []);
    }
}
