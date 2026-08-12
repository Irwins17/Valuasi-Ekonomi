<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Field, formula and output-preview definitions for the six Tabel 1
 * ecosystem-service modules.
 *
 * Each schema drives three things from a single declaration: the input form,
 * the formula panel beside it, and the output preview. `quantity` and `price`
 * name which input feeds each side of the shared
 * `value_per_ha = quantity × price × conversion` calculation, so the storage
 * layer never needs to know which service it is holding.
 *
 * Field types understood by the form renderer:
 *   text | number | currency | select | textarea | year
 * Extra per-field keys: required, placeholder, hint, suffix, prefix, options,
 * step, full (span both columns).
 *
 * Output `source` values: quantity | unit_price | value_per_ha | total_value
 * | quantity_area (quantity × area).
 */
class EcosystemServiceSchemas
{
    private const YEAR_HINT = 'Pilih periode / tahun';

    public const SCHEMAS = [
        'FOOD' => [
            'key' => 'FOOD',
            'name' => 'Food Production',
            'service_category' => 'provisioning',
            'code_prefix' => 'FP',
            'formula' => 'VPi = FPi × Pi',
            'formula_note' => 'Total lokasi = VPi × Luas Area',
            'legend' => [
                ['sym' => 'VPi', 'desc' => 'Value Production jenis ke-i per ha per tahun'],
                ['sym' => 'FPi', 'desc' => 'Food Production jenis ke-i per ha per tahun'],
                ['sym' => 'Pi', 'desc' => 'Price jenis ke-i'],
            ],
            'quantity' => ['name' => 'quantity_value', 'label' => 'Produksi per ha per tahun (FPi)', 'suffix' => 'kg/ha/tahun', 'placeholder' => 'Contoh: 1.250'],
            'price' => ['name' => 'unit_price', 'label' => 'Harga per Unit (Pi)', 'suffix' => 'Rp/unit', 'placeholder' => 'Contoh: 25.000'],
            'fields' => [
                ['name' => 'food_type', 'label' => 'Jenis Produk Pangan', 'type' => 'select', 'required' => true, 'placeholder' => '-- Pilih jenis produk pangan --', 'options' => ['Perikanan tangkap', 'Perikanan budidaya', 'Tanaman pangan', 'Hortikultura', 'Peternakan', 'Hasil hutan pangan', 'Lainnya']],
                ['name' => 'commodity', 'label' => 'Komoditas / Spesies', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: ikan, padi, belut, burung, dll.'],
                ['name' => 'location', 'label' => 'Lokasi / Ekosistem', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Tambak, Sawah, Rawa, Danau, Sungai, Mangrove'],
                ['name' => '@quantity'],
                ['name' => 'area_ha', 'label' => 'Luas Area (ha)', 'type' => 'number', 'required' => true, 'placeholder' => 'Contoh: 10,50', 'suffix' => 'ha'],
                ['name' => '@price'],
                ['name' => 'quantity_unit', 'label' => 'Satuan', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: kg, ton, ekor, ikat, liter, buah, dll.'],
                ['name' => 'period_year', 'label' => 'Periode / Tahun', 'type' => 'year', 'required' => true, 'placeholder' => self::YEAR_HINT],
                ['name' => 'data_source', 'label' => 'Sumber Data', 'type' => 'text', 'required' => true, 'full' => true, 'placeholder' => 'Contoh: Laporan survei, data produksi, publikasi, dokumen proyek, dll.'],
            ],
            'outputs' => [
                ['label' => 'Nilai Produksi per ha', 'sublabel' => 'VPi = FPi × Pi', 'source' => 'value_per_ha', 'format' => 'currency', 'unit' => 'per ha/tahun', 'color' => '#6366f1'],
                ['label' => 'Total Produksi', 'sublabel' => 'FPi × Luas Area', 'source' => 'quantity_area', 'format' => 'number', 'unit' => 'total per tahun', 'color' => '#f59e0b'],
                ['label' => 'Nilai Ekonomi Food Production', 'sublabel' => 'VPi × Luas Area', 'source' => 'total_value', 'format' => 'currency', 'unit' => 'per tahun', 'color' => '#10b981'],
            ],
        ],

        'RAWMAT' => [
            'key' => 'RAWMAT',
            'name' => 'Raw Material',
            'service_category' => 'provisioning',
            'code_prefix' => 'RT',
            'formula' => 'VRMi = RMPi × Pi',
            'formula_note' => 'Total lokasi = VRMi × Luas Area',
            'legend' => [
                ['sym' => 'VRMi', 'desc' => 'Nilai Raw Material per ha per tahun (Rp/ha/tahun)'],
                ['sym' => 'RMPi', 'desc' => 'Produksi atau pemanfaatan per ha per tahun dari material i'],
                ['sym' => 'Pi', 'desc' => 'Harga per unit material i (Rp/unit)'],
            ],
            'quantity' => ['name' => 'quantity_value', 'label' => 'Produksi / Pemanfaatan per ha per tahun (RMPi)', 'suffix' => 'unit/ha/tahun', 'placeholder' => 'Contoh: 12,50'],
            'price' => ['name' => 'unit_price', 'label' => 'Harga per Unit (Pi)', 'suffix' => 'Rp', 'placeholder' => 'Contoh: 25.000'],
            'fields' => [
                ['name' => 'utilization_type', 'label' => 'Jenis Pemanfaatan', 'type' => 'select', 'required' => true, 'placeholder' => '-- Pilih jenis pemanfaatan --', 'options' => ['Komersial', 'Subsisten / rumah tangga', 'Industri', 'Kerajinan', 'Konstruksi', 'Energi / kayu bakar', 'Lainnya']],
                ['name' => 'material_type', 'label' => 'Jenis Material', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: kayu, buah, daun, dll'],
                ['name' => 'location', 'label' => 'Lokasi / Ekosistem', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Hutan Alam, Hutan Mangrove, Kebun'],
                ['name' => '@quantity'],
                ['name' => 'area_ha', 'label' => 'Luas Area (ha)', 'type' => 'number', 'required' => true, 'placeholder' => 'Contoh: 10,00', 'suffix' => 'ha'],
                ['name' => '@price'],
                ['name' => 'quantity_unit', 'label' => 'Satuan', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: m³, kg, ton, batang, ikat'],
                ['name' => 'utilization_method', 'label' => 'Metode Pemanfaatan', 'type' => 'select', 'required' => true, 'placeholder' => '-- Pilih metode pemanfaatan --', 'options' => ['Panen langsung', 'Tebang pilih', 'Pemungutan hasil hutan bukan kayu', 'Budidaya', 'Lainnya']],
                ['name' => 'period_year', 'label' => 'Periode / Tahun', 'type' => 'year', 'required' => true, 'placeholder' => self::YEAR_HINT],
                ['name' => 'data_source', 'label' => 'Sumber Data', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Laporan survei, dokumen proyek, literatur'],
            ],
            'outputs' => [
                ['label' => 'Nilai Raw Material per ha', 'sublabel' => 'VRMi (Rp/ha/tahun)', 'source' => 'value_per_ha', 'format' => 'currency', 'unit' => 'Rp/ha/tahun', 'color' => '#6366f1'],
                ['label' => 'Total Pemanfaatan', 'sublabel' => 'RMPi × Luas Area (unit/tahun)', 'source' => 'quantity_area', 'format' => 'number', 'unit' => 'unit/tahun', 'color' => '#f59e0b'],
                ['label' => 'Nilai Ekonomi Raw Material', 'sublabel' => 'VRMi × Luas Area (Rp/tahun)', 'source' => 'total_value', 'format' => 'currency', 'unit' => 'Rp/tahun', 'color' => '#10b981'],
            ],
        ],

        'GENRES' => [
            'key' => 'GENRES',
            'name' => 'Genetic Resources',
            'service_category' => 'provisioning',
            'code_prefix' => 'GR',
            'formula' => 'VGRi = PGRi × Pi',
            'formula_note' => 'Total lokasi = VGRi × Luas Area',
            'legend' => [
                ['sym' => 'VGRi', 'desc' => 'Nilai sumber daya genetik per ha per tahun (Rp/ha/tahun)'],
                ['sym' => 'PGRi', 'desc' => 'Produksi sumber daya genetik per ha per tahun'],
                ['sym' => 'Pi', 'desc' => 'Harga per unit sumber daya genetik i (Rp/unit)'],
            ],
            'quantity' => ['name' => 'quantity_value', 'label' => 'Produksi Genetik per ha per tahun (PGRi)', 'suffix' => 'unit/ha/tahun', 'placeholder' => 'Contoh: 3,50'],
            'price' => ['name' => 'unit_price', 'label' => 'Harga per Unit (Pi)', 'suffix' => 'Rp', 'placeholder' => 'Contoh: 150.000'],
            'fields' => [
                ['name' => 'species', 'label' => 'Vegetasi / Spesies', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Rhizophora sp., anggrek hutan, rotan'],
                ['name' => 'part_used', 'label' => 'Bagian yang Dimanfaatkan', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: biji, akar, daun, kulit batang'],
                ['name' => 'utilization_form', 'label' => 'Bentuk Pemanfaatan', 'type' => 'select', 'required' => true, 'placeholder' => '-- Pilih bentuk pemanfaatan --', 'options' => ['Obat', 'Repository / plasma nutfah', 'Riset', 'Lainnya']],
                ['name' => 'location', 'label' => 'Lokasi / Ekosistem', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Hutan Alam, Hutan Mangrove, Kawasan Konservasi'],
                ['name' => '@quantity'],
                ['name' => 'area_ha', 'label' => 'Luas Area (ha)', 'type' => 'number', 'required' => true, 'placeholder' => 'Contoh: 25,00', 'suffix' => 'ha'],
                ['name' => '@price'],
                ['name' => 'quantity_unit', 'label' => 'Satuan', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: kg, batang, sampel'],
                ['name' => 'period_year', 'label' => 'Periode / Tahun', 'type' => 'year', 'required' => true, 'placeholder' => self::YEAR_HINT],
                ['name' => 'data_source', 'label' => 'Sumber Data', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Laporan riset, publikasi ilmiah, dokumen proyek'],
            ],
            'outputs' => [
                ['label' => 'Nilai Genetik per ha', 'sublabel' => 'VGRi (Rp/ha/tahun)', 'source' => 'value_per_ha', 'format' => 'currency', 'unit' => 'Rp/ha/tahun', 'color' => '#6366f1'],
                ['label' => 'Total Pemanfaatan', 'sublabel' => 'PGRi × Luas Area', 'source' => 'quantity_area', 'format' => 'number', 'unit' => 'unit/tahun', 'color' => '#f59e0b'],
                ['label' => 'Nilai Ekonomi Genetic Resources', 'sublabel' => 'VGRi × Luas Area', 'source' => 'total_value', 'format' => 'currency', 'unit' => 'Rp/tahun', 'color' => '#10b981'],
            ],
        ],

        'CLIMATE' => [
            'key' => 'CLIMATE',
            'name' => 'Climate / Carbon Storage',
            'service_category' => 'regulating',
            'code_prefix' => 'CS',
            'formula' => 'VCi = CSi × Pi',
            'formula_note' => 'Total lokasi = VCi × Luas Area',
            'legend' => [
                ['sym' => 'VCi', 'desc' => 'Nilai Ekonomi Climate / Carbon Storage (Rp/ha/tahun)'],
                ['sym' => 'CSi', 'desc' => 'Carbon Storage per ha per tahun (ton C/ha/tahun)'],
                ['sym' => 'Pi', 'desc' => 'Harga Karbon (Rp/ton karbon)'],
            ],
            'quantity' => ['name' => 'quantity_value', 'label' => 'Carbon Storage per ha per tahun (CSi)', 'suffix' => 'ton C/ha/tahun', 'placeholder' => 'Contoh: 3.25'],
            'price' => ['name' => 'unit_price', 'label' => 'Harga Karbon (Pi)', 'prefix' => 'Rp', 'placeholder' => 'Contoh: 100.000'],
            'fields' => [
                ['name' => 'vegetation_type', 'label' => 'Jenis Vegetasi', 'type' => 'select', 'required' => true, 'placeholder' => 'Pilih jenis vegetasi, contoh: mangrove, hutan', 'options' => ['Mangrove', 'Hutan hujan tropis', 'Hutan tanaman', 'Padang lamun', 'Rawa gambut', 'Savana', 'Lainnya']],
                ['name' => 'location', 'label' => 'Lokasi / Ekosistem', 'type' => 'text', 'required' => true, 'placeholder' => 'Pilih lokasi / ekosistem, contoh: mangrove pesisir, hutan hujan tropis'],
                ['name' => 'area_ha', 'label' => 'Luas Area (ha)', 'type' => 'number', 'required' => true, 'placeholder' => 'Contoh: 100.50', 'suffix' => 'ha'],
                ['name' => 'biomass_per_ha', 'label' => 'Biomassa / Kayu per ha (ton/ha)', 'type' => 'number', 'required' => false, 'placeholder' => 'Contoh: 150.00', 'suffix' => 'ton/ha', 'hint' => 'Data pendukung. Biomassa × faktor konversi dapat dipakai untuk memperkirakan CSi.'],
                ['name' => 'carbon_factor', 'label' => 'Faktor Konversi Karbon (ton C/ton biomassa)', 'type' => 'number', 'required' => false, 'placeholder' => 'Contoh: 0.47'],
                ['name' => '@quantity'],
                ['name' => '@price'],
                ['name' => 'quantity_unit', 'label' => 'Satuan Karbon', 'type' => 'select', 'required' => true, 'placeholder' => 'Pilih satuan karbon, contoh: ton CO₂e', 'options' => ['ton C', 'ton CO₂e', 'kg C']],
                ['name' => 'period_year', 'label' => 'Periode / Tahun', 'type' => 'year', 'required' => true, 'placeholder' => self::YEAR_HINT],
                ['name' => 'data_source', 'label' => 'Sumber Data', 'type' => 'text', 'required' => true, 'placeholder' => 'Pilih atau ketik sumber data'],
            ],
            'outputs' => [
                ['label' => 'Karbon Tersimpan', 'sublabel' => 'Estimasi ton C per ha per tahun', 'source' => 'quantity', 'format' => 'number', 'unit' => 'ton C/ha/tahun', 'color' => '#6366f1'],
                ['label' => 'Nilai Karbon per ha', 'sublabel' => 'Estimasi nilai karbon per ha per tahun', 'source' => 'value_per_ha', 'format' => 'currency', 'unit' => 'Rp/ha/tahun', 'color' => '#f59e0b'],
                ['label' => 'Nilai Ekonomi Climate', 'sublabel' => 'Estimasi nilai ekonomi total', 'source' => 'total_value', 'format' => 'currency', 'unit' => 'Rp/tahun', 'color' => '#10b981'],
            ],
        ],

        'EROSION' => [
            'key' => 'EROSION',
            'name' => 'Erosion Control',
            'service_category' => 'regulating',
            'code_prefix' => 'EC',
            'formula' => 'VACi = F × BPi',
            'formula_note' => 'Total lokasi = VACi × Luas Area Terdampak',
            'legend' => [
                ['sym' => 'VACi', 'desc' => 'Nilai pengendalian abrasi pada lokasi ke-i (Rp/tahun)'],
                ['sym' => 'F', 'desc' => 'Frekuensi kejadian erosi/abrasi per tahun (kali/tahun)'],
                ['sym' => 'BPi', 'desc' => 'Biaya penanganan abrasi per ha per tahun (Rp/ha/tahun)'],
            ],
            'quantity' => ['name' => 'quantity_value', 'label' => 'Frekuensi Kejadian per Tahun (F)', 'suffix' => 'kali/tahun', 'placeholder' => 'Contoh: 2,00'],
            'price' => ['name' => 'unit_price', 'label' => 'Biaya Penanganan Abrasi per ha per tahun (BPi)', 'suffix' => 'Rp/ha/tahun', 'placeholder' => 'Contoh: 12.500.000'],
            'fields' => [
                ['name' => 'location', 'label' => 'Lokasi Erosi / Abrasi', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Pantai Selatan, Desa Watu Karung'],
                ['name' => 'protector_ecosystem', 'label' => 'Jenis Ekosistem Pelindung', 'type' => 'select', 'required' => true, 'placeholder' => '-- Pilih jenis ekosistem pelindung --', 'options' => ['Mangrove', 'Terumbu karang', 'Padang lamun', 'Vegetasi pantai', 'Hutan lereng', 'Lainnya']],
                ['name' => 'eroded_area_ha', 'label' => 'Luasan Erosi / Abrasi (ha)', 'type' => 'number', 'required' => true, 'placeholder' => 'Contoh: 25,00', 'suffix' => 'ha'],
                ['name' => '@quantity'],
                ['name' => '@price'],
                ['name' => 'area_ha', 'label' => 'Luas Area Terdampak (ha)', 'type' => 'number', 'required' => true, 'placeholder' => 'Contoh: 50,00', 'suffix' => 'ha'],
                ['name' => 'period_year', 'label' => 'Periode / Tahun', 'type' => 'year', 'required' => true, 'placeholder' => self::YEAR_HINT],
                ['name' => 'data_source', 'label' => 'Sumber Data', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Laporan teknis, studi abrasi, dokumen proyek, dll.'],
            ],
            'outputs' => [
                ['label' => 'Frekuensi Kejadian', 'sublabel' => 'Frekuensi erosi/abrasi per tahun', 'source' => 'quantity', 'format' => 'number', 'unit' => 'kali/tahun', 'color' => '#6366f1'],
                ['label' => 'Biaya Penanganan', 'sublabel' => 'Biaya penanganan abrasi per ha', 'source' => 'unit_price', 'format' => 'currency', 'unit' => 'Rp/ha/tahun', 'color' => '#f59e0b'],
                ['label' => 'Nilai Pengendalian Abrasi (VACi)', 'sublabel' => 'VACi × Luas Area Terdampak', 'source' => 'total_value', 'format' => 'currency', 'unit' => 'Rp/tahun', 'color' => '#10b981'],
            ],
        ],

        'WATER' => [
            'key' => 'WATER',
            'name' => 'Water Supply',
            'service_category' => 'regulating',
            'code_prefix' => 'WS',
            'formula' => 'VWSi = WS × PWi',
            'formula_note' => 'Satuan diselaraskan otomatis: 1 m³ = 1.000 liter.',
            'legend' => [
                ['sym' => 'WS', 'desc' => 'Water Supply / Serapan Air per ha per tahun'],
                ['sym' => 'PWi', 'desc' => 'Harga Air per Liter'],
                ['sym' => 'VWSi', 'desc' => 'Nilai Water Supply per ekosistem i'],
            ],
            'quantity' => ['name' => 'quantity_value', 'label' => 'Water Supply / Serapan Air per ha per tahun (WS)', 'suffix' => 'm³/ha/tahun', 'placeholder' => 'Contoh: 850.00'],
            'price' => ['name' => 'unit_price', 'label' => 'Harga Air per Liter (PWi)', 'suffix' => 'Rp/Liter', 'placeholder' => 'Contoh: 25.00'],
            'fields' => [
                ['name' => 'ecosystem_type', 'label' => 'Jenis Ekosistem', 'type' => 'select', 'required' => true, 'placeholder' => '-- Pilih jenis ekosistem --', 'options' => ['Hutan hujan tropis', 'Hutan lindung', 'Daerah tangkapan air', 'Rawa gambut', 'Mangrove', 'Lainnya']],
                ['name' => 'location', 'label' => 'Lokasi / Ekosistem', 'type' => 'text', 'required' => true, 'placeholder' => '-- Pilih lokasi / ekosistem --'],
                ['name' => '@quantity'],
                ['name' => 'area_ha', 'label' => 'Luas Area (ha)', 'type' => 'number', 'required' => true, 'placeholder' => 'Contoh: 10.50', 'suffix' => 'ha'],
                ['name' => '@price'],
                ['name' => 'quantity_unit', 'label' => 'Satuan Output Air', 'type' => 'select', 'required' => true, 'placeholder' => 'Contoh: m³/tahun, Liter/tahun', 'options' => ['m³/tahun', 'Liter/tahun'], 'hint' => 'Jika satuan m³ sedangkan harga per liter, konversi 1 m³ = 1.000 liter diterapkan otomatis.'],
                ['name' => 'research_method', 'label' => 'Metode / Data Riset Serapan', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Studi hidrologi, Model InVEST, Literatur ilmiah'],
                ['name' => 'period_year', 'label' => 'Periode / Tahun', 'type' => 'year', 'required' => true, 'placeholder' => self::YEAR_HINT],
                ['name' => 'data_source', 'label' => 'Sumber Data', 'type' => 'text', 'required' => true, 'full' => true, 'placeholder' => 'Contoh: Laporan penelitian, dokumen proyek, jurnal, dll.'],
            ],
            'outputs' => [
                ['label' => 'Debit / Stok Air', 'sublabel' => 'Estimasi total air terserap', 'source' => 'quantity_area', 'format' => 'number', 'unit' => 'm³/tahun', 'color' => '#3b82f6'],
                ['label' => 'Nilai per ha', 'sublabel' => 'Nilai Water Supply per ha', 'source' => 'value_per_ha', 'format' => 'currency', 'unit' => '/ha/tahun', 'color' => '#10b981'],
                ['label' => 'Nilai Ekonomi Water Supply', 'sublabel' => 'Nilai total Water Supply', 'source' => 'total_value', 'format' => 'currency', 'unit' => 'Rp/tahun', 'color' => '#f59e0b'],
            ],
        ],
    ];

    /** Fields stored in the `extra` JSON column, per service. */
    private const TYPED_COLUMNS = [
        'record_code', 'location', 'quantity_value', 'quantity_unit',
        'unit_price', 'area_ha', 'period_year', 'data_source', 'notes',
    ];

    public static function keys(): array
    {
        return array_keys(self::SCHEMAS);
    }

    public static function find(string $key): ?array
    {
        return self::SCHEMAS[$key] ?? null;
    }

    /**
     * Expands the `@quantity` / `@price` placeholders so the form renderer
     * receives one flat, ordered field list.
     */
    public static function resolvedFields(string $key): array
    {
        $schema = self::find($key);
        if (! $schema) {
            return [];
        }

        $fields = [];

        foreach ($schema['fields'] as $field) {
            if ($field['name'] === '@quantity') {
                $fields[] = ['type' => 'number', 'required' => true, 'step' => 'any'] + $schema['quantity'];
            } elseif ($field['name'] === '@price') {
                $fields[] = ['type' => 'currency', 'required' => true] + $schema['price'];
            } else {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /** Names of the schema's fields that belong in the `extra` JSON column. */
    public static function extraFieldNames(string $key): array
    {
        return collect(self::resolvedFields($key))
            ->pluck('name')
            ->reject(fn ($name) => in_array($name, self::TYPED_COLUMNS, true))
            ->values()
            ->all();
    }

    /** Schema payload for Inertia, with placeholders already expanded. */
    public static function forFrontend(string $key): array
    {
        $schema = self::find($key);

        return [...$schema, 'fields' => self::resolvedFields($key)];
    }

    /**
     * Factor that reconciles the quantity's unit with the price's unit.
     *
     * Only Water Supply currently needs one: serapan air is recorded in m³
     * while the tariff is quoted per litre, so multiplying the two directly
     * would under-report the value a thousandfold. Every other service quotes
     * both sides in the same unit and stays at 1.
     *
     * This lives with the schemas rather than in the calculator because it is
     * a property of how a service is measured, not of the arithmetic — the
     * calculator simply applies whatever factor it is handed.
     */
    public static function priceConversion(string $serviceKey, ?string $quantityUnit): float
    {
        if ($serviceKey !== 'WATER') {
            return 1.0;
        }

        return Str::contains((string) $quantityUnit, ['m³', 'm3']) ? 1000.0 : 1.0;
    }
}
