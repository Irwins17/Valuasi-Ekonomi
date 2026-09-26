<?php

namespace App\Support;

/**
 * Built-in valuation modules every project starts with.
 *
 * This is the source of truth for a module's identity (code, method, formula,
 * which ecosystem service it covers) and for where its data-entry pages live.
 * Per-project tweaks — status, visibility on the project detail page, notes —
 * are stored as override rows in `valuation_modules`; see
 * App\Models\ValuationModule::resolveForProject().
 *
 * `route` is the module's index route name, or null while the module has no
 * data-entry pages yet — those render with a disabled "Buka" button.
 * `countRelation` is the Project relation counted for "Jumlah Data".
 */
class ValuationModuleCatalog
{
    public const METHOD_GROUPS = [
        'direct_use' => 'Direct Use',
        'revealed_preference' => 'Revealed Preference',
        'stated_preference' => 'Stated Preference',
        'cost_based' => 'Cost Based',
        'benefit_transfer' => 'Benefit Transfer',
    ];

    public const SERVICE_CATEGORIES = [
        'provisioning' => 'Provisioning',
        'regulating' => 'Regulating',
        'supporting' => 'Supporting',
        'cultural' => 'Cultural',
    ];

    public const STATUSES = [
        'aktif' => 'Aktif',
        'draft' => 'Draft',
    ];

    public const MODULES = [
        [
            'code' => 'DUV',
            'name' => 'Nilai Pasar (Market Price)',
            'description' => 'Metode Market Price — kuantitas panen/tangkapan dikali harga pasar, dikurangi biaya. Metode ini mengisi kategori nilai TEV "Direct Use Value" (lihat Langkah 7).',
            'valuation_method' => 'Market Price',
            'method_group' => 'direct_use',
            'service_category' => 'provisioning',
            'subcategory' => 'Nilai guna langsung (gross & net)',
            'formula_summary' => "Nilai kotor = Σ(Qi × Pi)\nNilai bersih = Σ(Qi × Pi) − Ci",
            'input_variables' => 'Kuantitas (Qi), Harga pasar (Pi), Biaya produksi (Ci)',
            'output_unit' => 'Rp/tahun',
            'route' => 'admin.modules.duv.index',
            'countRelation' => 'duvData',
            'advanced' => false,
            'notes' => 'Metode Nilai Pasar (Market Price) untuk menghitung Direct Use Value — kategori nilai TEV, bukan nama metodenya sendiri.',
        ],
        [
            'code' => 'EOP',
            'name' => 'EOP',
            'description' => 'Effect on Production — perubahan volume produksi & harga pasar.',
            'valuation_method' => 'EOP',
            'method_group' => 'direct_use',
            'service_category' => 'provisioning',
            'subcategory' => 'Perubahan produktivitas',
            'formula_summary' => "ΔQ = Q sesudah − Q sebelum\nNilai kotor = ΔQ × Harga\nNilai bersih = Nilai kotor − Biaya produksi",
            'input_variables' => 'Produksi sebelum/sesudah, Harga pasar, Biaya produksi, Luas area',
            'output_unit' => 'Rp/tahun',
            'route' => 'admin.modules.eop.index',
            'countRelation' => 'eopData',
            'advanced' => false,
            'notes' => 'Digunakan untuk menilai dampak perubahan lingkungan terhadap produksi dan harga pasar komoditas.',
        ],
        [
            'code' => 'TCM',
            'name' => 'TCM',
            'description' => 'Travel Cost Method — biaya perjalanan pengunjung ke lokasi.',
            'valuation_method' => 'TCM',
            'method_group' => 'revealed_preference',
            'service_category' => 'cultural',
            'subcategory' => 'Nilai rekreasi',
            'formula_summary' => "TC = transport + tiket + (nilai waktu × waktu tempuh)\nCS = −1 / β₁\nNilai rekreasi = CS × total pengunjung",
            'input_variables' => 'Frekuensi kunjungan, Biaya perjalanan, Waktu tempuh, Pendapatan, Usia, Pendidikan',
            'output_unit' => 'Rp/tahun',
            'route' => 'admin.modules.tcm.index',
            'countRelation' => 'tcmData',
            'advanced' => false,
            'notes' => 'Digunakan untuk menilai nilai rekreasi berdasarkan biaya perjalanan yang dikeluarkan pengunjung.',
        ],
        [
            'code' => 'CVM',
            'name' => 'CVM',
            'description' => 'Contingent Valuation Method — kesediaan membayar (WTP).',
            'valuation_method' => 'CVM',
            'method_group' => 'stated_preference',
            'service_category' => 'cultural',
            'subcategory' => 'Nilai non-pasar (WTP/WTA)',
            'formula_summary' => "Mean WTP = ΣWTP / n\nTotal WTP = Mean WTP × Populasi",
            'input_variables' => 'Bid amount, Kesediaan membayar, WTP aktual, Pendapatan RT, Usia, Pendidikan',
            'output_unit' => 'Rp/tahun',
            'route' => 'admin.modules.cvm.index',
            'countRelation' => 'cvmData',
            'advanced' => false,
            'notes' => 'Digunakan untuk menilai kesediaan membayar (WTP) responden terhadap perbaikan/pelestarian lingkungan.',
        ],
        [
            'code' => 'HPM',
            'name' => 'HPM',
            'description' => 'Hedonic Pricing Method — nilai jasa melalui perubahan harga pasar.',
            'valuation_method' => 'HPM',
            'method_group' => 'revealed_preference',
            'service_category' => 'regulating',
            'subcategory' => 'Harga implisit lingkungan',
            'formula_summary' => "ln Pₕ = α₀ + βS + γN + δE + e\nMWTP = ∂P/∂E = δ × Pₕ\nNilai agregat = MWTP × ΔE × M",
            'input_variables' => 'Harga properti, Atribut struktural, Atribut lingkungan (AQI, kebisingan, jarak RTH)',
            'output_unit' => 'Rp',
            'route' => 'admin.modules.hpm.index',
            'countRelation' => 'hpmData',
            'advanced' => true,
            'notes' => 'Digunakan untuk menilai jasa lingkungan yang terkapitalisasi ke dalam harga properti.',
        ],
        [
            'code' => 'ABM',
            'name' => 'ABM / Defensive Expenditure',
            'description' => 'Abatement/Defensive Behaviour — biaya tindakan pencegahan kerusakan.',
            'valuation_method' => 'ABM',
            'method_group' => 'revealed_preference',
            'service_category' => 'regulating',
            'subcategory' => 'Biaya pencegahan & penghindaran',
            'formula_summary' => "Nilai averting = Σ(Q × P) + biaya waktu\nPendapatan hilang = hari sakit × upah harian\nTotal avoidance = biaya defensif + biaya medis + pendapatan hilang",
            'input_variables' => 'Tindakan defensif, Kuantitas & harga, Biaya medis, Hari sakit, Upah harian',
            'output_unit' => 'Rp/tahun',
            'route' => 'admin.modules.abm.index',
            'countRelation' => 'abmData',
            'advanced' => true,
            'notes' => 'Digunakan untuk menilai kerugian lingkungan dari biaya yang dikeluarkan rumah tangga untuk menghindarinya.',
        ],
        [
            'code' => 'CE',
            'name' => 'Choice Experiment (CE)',
            'description' => 'Choice Experiment — preferensi pilihan atribut lingkungan.',
            'valuation_method' => 'CE',
            'method_group' => 'stated_preference',
            'service_category' => 'cultural',
            'subcategory' => 'Preferensi multi-atribut',
            'formula_summary' => "Uᵢⱼₜ = Vᵢⱼₜ + εᵢⱼₜ\nPᵢⱼₜ = exp(Vᵢⱼₜ) / Σ exp(Vᵢₘₜ)\nMWTPₖ = −βₖ / βₚ",
            'input_variables' => 'Choice set, Atribut & level, Biaya/retribusi, Karakteristik responden',
            'output_unit' => 'Rp',
            'route' => 'admin.modules.ce.index',
            'countRelation' => 'ceData',
            'advanced' => true,
            'notes' => 'Digunakan untuk menilai preferensi masyarakat terhadap kombinasi atribut lingkungan dan biayanya.',
        ],
        [
            'code' => 'RCM',
            'name' => 'Replacement Cost (Metode Mandiri)',
            'description' => 'Replacement Cost Method — biaya mengganti aset/infrastruktur alami dengan aset buatan setara, dianualisasi.',
            'valuation_method' => 'Replacement Cost',
            'method_group' => 'cost_based',
            'service_category' => 'regulating',
            'subcategory' => 'Biaya penggantian aset alami',
            'formula_summary' => "Nilai total = Volume/Luas × Biaya Penggantian per Unit\nNilai tahunan = Nilai total ÷ Umur Manfaat",
            'input_variables' => 'Volume/luas aset yang digantikan, Biaya penggantian per unit, Umur manfaat (tahun)',
            'output_unit' => 'Rp/tahun',
            'route' => 'admin.modules.rcm.index',
            'countRelation' => 'rcmData',
            'advanced' => false,
            'notes' => 'Modul mandiri Replacement Cost — terpisah dari label "Replacement Cost" yang dipakai modul EROSION khusus untuk pengendalian abrasi.',
        ],
        [
            'code' => 'ADC',
            'name' => 'Avoided Damage Cost',
            'description' => 'Nilai kerusakan yang berhasil dihindari berkat keberadaan ekosistem, pada skala proyek/kawasan.',
            'valuation_method' => 'Avoided Damage Cost',
            'method_group' => 'cost_based',
            'service_category' => 'regulating',
            'subcategory' => 'Kerusakan yang dihindari',
            'formula_summary' => 'ADC = Luas Terlindungi × Biaya Kerusakan per Unit × Probabilitas Kejadian',
            'input_variables' => 'Luas area terlindungi, Estimasi biaya kerusakan per unit, Probabilitas kejadian per tahun',
            'output_unit' => 'Rp/tahun',
            'route' => 'admin.modules.adc.index',
            'countRelation' => 'adcData',
            'advanced' => false,
            'notes' => 'Berbeda dari ABM: ABM adalah defensive expenditure tingkat rumah tangga (revealed preference), ADC adalah biaya kerusakan tingkat kawasan yang dihindari (cost based).',
        ],
        [
            'code' => 'BTM',
            'name' => 'Benefit Transfer',
            'description' => 'Mentransfer nilai dari studi valuasi lain ke lokasi proyek, dengan penyesuaian konteks.',
            'valuation_method' => 'Benefit Transfer',
            'method_group' => 'benefit_transfer',
            'service_category' => 'regulating',
            'subcategory' => 'Transfer nilai dari studi lain',
            'formula_summary' => 'Nilai transfer = Nilai Studi Sumber × Faktor Penyesuaian × Kuantitas Target',
            'input_variables' => 'Nilai studi sumber, Faktor penyesuaian (indeks harga/PPP/pendapatan), Kuantitas/luas lokasi target',
            'output_unit' => 'Rp',
            'route' => 'admin.modules.btm.index',
            'countRelation' => 'btmData',
            'advanced' => true,
            'notes' => 'Digunakan ketika pengukuran langsung tidak tersedia; validitas transfer bergantung pada kesesuaian konteks studi sumber dan lokasi target.',
        ],

        // --- Formulasi jasa ekosistem (Tabel 1) ---
        [
            'code' => 'FOOD',
            'name' => 'Food Production',
            'description' => 'Nilai produksi pangan per hektar per tahun dari ekosistem.',
            'valuation_method' => 'Harga Pasar',
            'method_group' => 'direct_use',
            'service_category' => 'provisioning',
            'subcategory' => 'Produksi pangan',
            'formula_summary' => 'VPi = FPi × Pi',
            'input_variables' => 'Food production (FPi), Harga (Pi), Luas area',
            'output_unit' => 'Rp/ha/tahun',
            'route' => 'admin.modules.ecosystem.index',
            'routeParams' => ['FOOD'],
            'serviceKey' => 'FOOD',
            'countRelation' => null,
            'advanced' => false,
            'notes' => 'Menilai hasil pangan (ikan, padi, dll.) yang dihasilkan ekosistem.',
        ],
        [
            'code' => 'RAWMAT',
            'name' => 'Raw Material',
            'description' => 'Nilai bahan baku (kayu, daun, buah) yang dimanfaatkan dari ekosistem.',
            'valuation_method' => 'Harga Pasar',
            'method_group' => 'direct_use',
            'service_category' => 'provisioning',
            'subcategory' => 'Bahan baku',
            'formula_summary' => 'VRMi = RMPi × Pi',
            'input_variables' => 'Produksi/pemanfaatan (RMPi), Harga (Pi), Luas area',
            'output_unit' => 'Rp/ha/tahun',
            'route' => 'admin.modules.ecosystem.index',
            'routeParams' => ['RAWMAT'],
            'serviceKey' => 'RAWMAT',
            'countRelation' => null,
            'advanced' => false,
            'notes' => 'Menilai pemanfaatan material non-pangan dari ekosistem.',
        ],
        [
            'code' => 'GENRES',
            'name' => 'Genetic Resources',
            'description' => 'Nilai sumber daya genetik untuk obat, riset, dan repository.',
            'valuation_method' => 'Harga Pasar',
            'method_group' => 'direct_use',
            'service_category' => 'provisioning',
            'subcategory' => 'Sumber daya genetik',
            'formula_summary' => 'VGRi = PGRi × Pi',
            'input_variables' => 'Produksi genetik (PGRi), Harga (Pi), Luas area',
            'output_unit' => 'Rp/ha/tahun',
            'route' => 'admin.modules.ecosystem.index',
            'routeParams' => ['GENRES'],
            'serviceKey' => 'GENRES',
            'countRelation' => null,
            'advanced' => false,
            'notes' => 'Menilai pemanfaatan plasma nutfah untuk obat, riset, dan konservasi.',
        ],
        [
            'code' => 'CLIMATE',
            'name' => 'Climate / Carbon Storage',
            'description' => 'Nilai simpanan karbon dari vegetasi ekosistem.',
            'valuation_method' => 'Harga Karbon',
            'method_group' => 'cost_based',
            'service_category' => 'regulating',
            'subcategory' => 'Penyimpanan karbon',
            'formula_summary' => 'VCi = CSi × Pi',
            'input_variables' => 'Biomassa, Faktor konversi karbon, Carbon storage (CSi), Harga karbon (Pi)',
            'output_unit' => 'Rp/ha/tahun',
            'route' => 'admin.modules.ecosystem.index',
            'routeParams' => ['CLIMATE'],
            'serviceKey' => 'CLIMATE',
            'countRelation' => null,
            'advanced' => false,
            'notes' => 'Menilai jasa pengaturan iklim melalui karbon yang tersimpan pada vegetasi.',
        ],
        [
            'code' => 'EROSION',
            'name' => 'Erosion Control',
            'description' => 'Nilai pengendalian erosi/abrasi oleh ekosistem pelindung.',
            'valuation_method' => 'Replacement Cost',
            'method_group' => 'cost_based',
            'service_category' => 'regulating',
            'subcategory' => 'Pengendalian erosi/abrasi',
            'formula_summary' => 'VACi = F × BPi',
            'input_variables' => 'Frekuensi kejadian (F), Biaya penanganan abrasi (BPi), Luas terdampak',
            'output_unit' => 'Rp/tahun',
            'route' => 'admin.modules.ecosystem.index',
            'routeParams' => ['EROSION'],
            'serviceKey' => 'EROSION',
            'countRelation' => null,
            'advanced' => false,
            'notes' => 'Menilai jasa perlindungan pantai/lereng dari biaya penanganan abrasi yang dihindari.',
        ],
        [
            'code' => 'HAB',
            'name' => 'Fungsi Habitat & Siklus Hara',
            'description' => 'Nilai fungsi habitat/nursery ground dan siklus hara yang disediakan ekosistem — kategori jasa ekosistem Supporting.',
            'valuation_method' => 'Replacement Cost',
            'method_group' => 'cost_based',
            'service_category' => 'supporting',
            'subcategory' => 'Fungsi habitat & siklus hara',
            'formula_summary' => 'VHi = Indeks Kondisi × Nilai Pengganti per ha × Luas Area',
            'input_variables' => 'Indeks kondisi ekosistem (0–1), Nilai pengganti fungsi per ha (PHi/PNCi), Luas area (LAj)',
            'output_unit' => 'Rp/tahun',
            'route' => 'admin.modules.ecosystem.index',
            'routeParams' => ['HABITAT'],
            'serviceKey' => 'HABITAT',
            'countRelation' => null,
            'advanced' => false,
            'notes' => 'Modul pertama yang mengisi kategori jasa ekosistem "Supporting" — sebelumnya kategori ini terdaftar tapi tidak ada modul yang memakainya.',
        ],
        [
            'code' => 'WATER',
            'name' => 'Water Supply',
            'description' => 'Nilai penyediaan & serapan air oleh ekosistem.',
            'valuation_method' => 'Harga Air',
            'method_group' => 'cost_based',
            'service_category' => 'regulating',
            'subcategory' => 'Penyediaan air',
            'formula_summary' => 'VWSi = WS × PWi',
            'input_variables' => 'Water supply (WS), Harga air (PWi), Luas area',
            'output_unit' => 'Rp/ha/tahun',
            'route' => 'admin.modules.ecosystem.index',
            'routeParams' => ['WATER'],
            'serviceKey' => 'WATER',
            'countRelation' => null,
            'advanced' => false,
            'notes' => 'Menilai jasa tata air; satuan m³ dikonversi otomatis ke liter (1 m³ = 1.000 liter).',
        ],
    ];

    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return self::MODULES;
    }

    public static function find(string $code): ?array
    {
        foreach (self::MODULES as $module) {
            if ($module['code'] === $code) {
                return $module;
            }
        }

        return null;
    }

    /** @return array<int, string> */
    public static function codes(): array
    {
        return array_column(self::MODULES, 'code');
    }
}
