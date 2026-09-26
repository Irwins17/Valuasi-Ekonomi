<?php

namespace App\Support;

/**
 * The ecosystem object types a project can be classified under (flowchart
 * Langkah 2 — "Menentukan Objek dan Batas Wilayah"). Mirrored in
 * resources/js/lib/ecosystemObjectTypes.js.
 */
class EcosystemObjectTypes
{
    public const OPTIONS = [
        'mangrove' => 'Mangrove',
        'terumbu_karang' => 'Terumbu Karang',
        'lamun' => 'Lamun',
        'perikanan_tangkap' => 'Perikanan Tangkap',
        'perikanan_budidaya' => 'Perikanan Budidaya',
        'kawasan_konservasi_laut' => 'Kawasan Konservasi Laut',
    ];
}
