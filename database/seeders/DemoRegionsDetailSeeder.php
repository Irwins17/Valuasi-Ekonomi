<?php

namespace Database\Seeders;

use App\Models\AbmData;
use App\Models\Benefit;
use App\Models\CeData;
use App\Models\DuvData;
use App\Models\EcosystemServiceRecord;
use App\Models\HpmData;
use App\Models\Project;
use App\Services\Valuation\EconomicValuationCalculator;
use App\Support\EcosystemServiceSchemas;
use Illuminate\Database\Seeder;

/**
 * Fleshes out the six DEMO- projects from DemoRegionsSeeder with the modules
 * that were left at "Draft" (0 records): DUV, HPM, ABM, CE, and all six
 * Tabel-1 ecosystem-service types (FOOD/RAWMAT/GENRES/CLIMATE/EROSION/WATER).
 *
 * Split from DemoRegionsSeeder rather than folded into it because it depends
 * on those six projects already existing (`Project::where('code', ...)`) —
 * running it alone against a database that never ran DemoRegionsSeeder is a
 * silent no-op, not an error.
 *
 * DUV and the ecosystem-service records each also get a matching Benefit row
 * (source_module tagged), since both are directly monetizable per-hectare
 * values — the same way EOP/TCM/CVM already feed the Manfaat table. HPM, ABM
 * and CE do not: their totals are a regression coefficient (HPM, CE) or an
 * avoided-cost figure that reads as a cost saved rather than a benefit
 * earned (ABM), neither of which this seeder fabricates — they are left as
 * raw module data for the "Status Modul" tiles and the module's own pages.
 */
class DemoRegionsDetailSeeder extends Seeder
{
    private EconomicValuationCalculator $calc;

    public function run(): void
    {
        $this->calc = new EconomicValuationCalculator;

        $this->seedDetail('DEMO-001', $this->karstGunungSewuDetail());
        $this->seedDetail('DEMO-002', $this->wayKambasDetail());
        $this->seedDetail('DEMO-003', $this->segaraAnakanDetail());
        $this->seedDetail('DEMO-004', $this->danauSentarumDetail());
        $this->seedDetail('DEMO-005', $this->bunakenDetail());
        $this->seedDetail('DEMO-006', $this->kelimutuDetail());
    }

    // ── region detail configs ───────────────────────────────────────────

    private function karstGunungSewuDetail(): array
    {
        return [
            'duv' => [
                ['Madu Hutan Karst', 'Hutan Wonosadi, Gunungkidul', 750, 'Kg', 180000, 20000],
                ['Empon-empon (tanaman obat) lahan karst', 'Playen, Gunungkidul', 3200, 'Kg', 35000, 8000],
            ],
            'ecosystem' => [
                'FOOD' => ['Ladang karst Gunungkidul', 850, 'kg/ha/tahun', 4500, 120, [
                    'food_type' => 'Tanaman pangan', 'commodity' => 'Jagung & ketela lahan karst',
                ]],
                'RAWMAT' => ['Sentra kerajinan batu Gunungkidul', 45, 'kg/ha/tahun', 250000, 15, [
                    'utilization_type' => 'Kerajinan', 'material_type' => 'Batu putih/onyx karst', 'utilization_method' => 'Pemungutan hasil hutan bukan kayu',
                ]],
                'GENRES' => ['Kawasan konservasi karst Nglanggeran', 2.1, 'kg/ha/tahun', 200000, 80, [
                    'species' => 'Anggrek & tanaman obat endemik karst', 'part_used' => 'Umbi/daun', 'utilization_form' => 'Riset',
                ]],
                'CLIMATE' => ['Hutan rakyat sekitar kawasan karst', 2.8, 'ton C', 95000, 350, [
                    'vegetation_type' => 'Hutan tanaman', 'biomass_per_ha' => 95, 'carbon_factor' => 0.47,
                ]],
                'EROSION' => ['Lereng karst rawan longsor Nglanggeran', 1.5, null, 9500000, 30, [
                    'protector_ecosystem' => 'Hutan lereng', 'eroded_area_ha' => 18,
                ]],
                'WATER' => ['Sistem akuifer karst Bribin', 1250, 'm³/tahun', 22, 200, [
                    'ecosystem_type' => 'Daerah tangkapan air', 'research_method' => 'Studi hidrologi karst',
                ]],
            ],
            'hpm' => [
                'locations' => ['Desa Bleberan', 'Nglanggeran', 'Semanu', 'Playen', 'Ponjong', 'Patuk'],
                'types' => ['Rumah tinggal', 'Homestay', 'Tanah kavling'],
            ],
            'abm' => [
                'risk_type' => 'Kekeringan / krisis air bersih musim kemarau',
                'defensive_action' => 'Beli air tangki & bangun tandon penampungan',
                'defensive_goods' => 'Air tangki & tandon',
                'locations' => ['Playen', 'Semanu', 'Ponjong', 'Tanjungsari', 'Girisubo'],
            ],
            'ce' => [
                'scenario_title' => 'Preferensi pengelolaan wisata geopark karst',
                'attribute_1' => 'Luas kawasan konservasi geopark', 'levels_1' => ['500 ha', '1.000 ha'],
                'attribute_2' => 'Frekuensi patroli/pengawasan', 'levels_2' => ['Bulanan', 'Mingguan'],
                'locations' => ['Wonosari', 'Playen', 'Semanu', 'Patuk', 'Nglanggeran', 'Yogyakarta', 'Gedangsari', 'Panggang'],
            ],
        ];
    }

    private function wayKambasDetail(): array
    {
        return [
            'duv' => [
                ['Rotan hutan rawa', 'Kawasan penyangga Way Kambas', 4200, 'Kg', 12000, 2500],
                ['Ikan rawa (non-EOP, tangkapan insidental)', 'Rawa penyangga taman nasional', 1800, 'Kg', 28000, 4000],
            ],
            'ecosystem' => [
                'FOOD' => ['Rawa & sungai penyangga Way Kambas', 620, 'kg/ha/tahun', 22000, 180, [
                    'food_type' => 'Perikanan tangkap', 'commodity' => 'Ikan rawa & udang air tawar',
                ]],
                'RAWMAT' => ['Hutan rawa penyangga', 38, 'kg/ha/tahun', 12000, 90, [
                    'utilization_type' => 'Subsisten / rumah tangga', 'material_type' => 'Rotan & bambu', 'utilization_method' => 'Pemungutan hasil hutan bukan kayu',
                ]],
                'GENRES' => ['Kawasan konservasi Way Kambas', 1.4, 'kg/ha/tahun', 175000, 60, [
                    'species' => 'Tanaman obat hutan rawa', 'part_used' => 'Kulit batang/akar', 'utilization_form' => 'Obat',
                ]],
                'CLIMATE' => ['Hutan rawa gambut Way Kambas', 4.6, 'ton C', 105000, 900, [
                    'vegetation_type' => 'Rawa gambut', 'biomass_per_ha' => 180, 'carbon_factor' => 0.47,
                ]],
                'EROSION' => ['Tepian sungai Way Kambas', 1.2, null, 7200000, 40, [
                    'protector_ecosystem' => 'Vegetasi pantai', 'eroded_area_ha' => 22,
                ]],
                'WATER' => ['Daerah tangkapan air rawa Way Kambas', 980, 'm³/tahun', 20, 260, [
                    'ecosystem_type' => 'Rawa gambut', 'research_method' => 'Studi hidrologi DAS Way Kambas',
                ]],
            ],
            'hpm' => [
                'locations' => ['Labuhan Ratu', 'Way Jepara', 'Braja Harjosari', 'Purwodadi', 'Rajabasa Lama', 'Labuhan Ratu Baru'],
                'types' => ['Rumah tinggal', 'Tanah kavling', 'Lahan pertanian'],
            ],
            'abm' => [
                'risk_type' => 'Konflik satwa (gajah keluar kawasan)',
                'defensive_action' => 'Pagar listrik / parit gajah swadaya',
                'defensive_goods' => 'Material pagar & alat pengusir',
                'locations' => ['Labuhan Ratu', 'Way Jepara', 'Braja Harjosari', 'Purwodadi', 'Rajabasa Lama'],
            ],
            'ce' => [
                'scenario_title' => 'Preferensi program mitigasi konflik gajah-manusia',
                'attribute_1' => 'Panjang pagar listrik terpasang', 'levels_1' => ['10 km', '25 km'],
                'attribute_2' => 'Kompensasi kerusakan panen', 'levels_2' => ['Tidak ada', 'Ada, dana desa'],
                'locations' => ['Labuhan Ratu', 'Way Jepara', 'Braja Harjosari', 'Purwodadi', 'Rajabasa Lama', 'Metro', 'Bandar Lampung', 'Labuhan Ratu'],
            ],
        ];
    }

    private function segaraAnakanDetail(): array
    {
        return [
            'duv' => [
                ['Nener/benih bandeng alami', 'Perairan Segara Anakan', 320000, 'Ekor', 350, 40],
                ['Kayu bakar mangrove (subsisten)', 'Hutan mangrove Segara Anakan', 2600, 'Kg', 3500, 500],
            ],
            'ecosystem' => [
                'FOOD' => ['Tambak & perairan Segara Anakan', 540, 'kg/ha/tahun', 65000, 220, [
                    'food_type' => 'Perikanan budidaya', 'commodity' => 'Bandeng, udang, kepiting bakau',
                ]],
                'RAWMAT' => ['Hutan mangrove Segara Anakan', 28, 'kg/ha/tahun', 3500, 150, [
                    'utilization_type' => 'Energi / kayu bakar', 'material_type' => 'Kayu mangrove', 'utilization_method' => 'Tebang pilih',
                ]],
                'GENRES' => ['Hutan mangrove Segara Anakan', 0.9, 'kg/ha/tahun', 220000, 100, [
                    'species' => 'Rhizophora sp. & Avicennia sp.', 'part_used' => 'Propagul/daun', 'utilization_form' => 'Repository / plasma nutfah',
                ]],
                'CLIMATE' => ['Hutan mangrove Segara Anakan', 6.8, 'ton C', 110000, 400, [
                    'vegetation_type' => 'Mangrove', 'biomass_per_ha' => 210, 'carbon_factor' => 0.47,
                ]],
                'EROSION' => ['Pesisir Segara Anakan / Nusakambangan', 2.5, null, 14000000, 65, [
                    'protector_ecosystem' => 'Mangrove', 'eroded_area_ha' => 35,
                ]],
                'WATER' => ['Estuari Segara Anakan', 1450, 'm³/tahun', 24, 300, [
                    'ecosystem_type' => 'Mangrove', 'research_method' => 'Studi hidrologi estuari Citanduy',
                ]],
            ],
            'hpm' => [
                'locations' => ['Kampung Laut', 'Klaces', 'Ujung Alang', 'Ujung Gagak', 'Panikel', 'Cilacap'],
                'types' => ['Rumah tinggal', 'Tanah tambak', 'Rumah panggung'],
            ],
            'abm' => [
                'risk_type' => 'Rob & pendangkalan mempersulit akses perahu',
                'defensive_action' => 'Tinggikan lantai rumah / beli perahu cadangan',
                'defensive_goods' => 'Material rumah panggung & perahu',
                'locations' => ['Kampung Laut', 'Klaces', 'Ujung Alang', 'Ujung Gagak', 'Panikel'],
            ],
            'ce' => [
                'scenario_title' => 'Preferensi program rehabilitasi mangrove',
                'attribute_1' => 'Luas rehabilitasi mangrove per tahun', 'levels_1' => ['50 ha', '150 ha'],
                'attribute_2' => 'Pelibatan kelompok nelayan', 'levels_2' => ['Tidak dilibatkan', 'Dilibatkan penuh'],
                'locations' => ['Kampung Laut', 'Klaces', 'Ujung Alang', 'Ujung Gagak', 'Panikel', 'Cilacap', 'Kawunganten', 'Kampung Laut'],
            ],
        ];
    }

    private function danauSentarumDetail(): array
    {
        return [
            'duv' => [
                ['Madu hutan Danau Sentarum (Apis dorsata)', 'Hutan tepian Danau Sentarum', 9500, 'Kg', 220000, 25000],
                ['Ikan asin/salai olahan rumah tangga', 'Sekitar Danau Sentarum', 5200, 'Kg', 45000, 12000],
            ],
            'ecosystem' => [
                'FOOD' => ['Danau & rawa gambut Sentarum', 780, 'kg/ha/tahun', 26000, 260, [
                    'food_type' => 'Perikanan tangkap', 'commodity' => 'Ikan air tawar danau musiman',
                ]],
                'RAWMAT' => ['Hutan tepian Danau Sentarum', 32, 'kg/ha/tahun', 15000, 110, [
                    'utilization_type' => 'Komersial', 'material_type' => 'Rotan & madu sarang', 'utilization_method' => 'Pemungutan hasil hutan bukan kayu',
                ]],
                'GENRES' => ['Kawasan konservasi Danau Sentarum', 1.1, 'kg/ha/tahun', 160000, 70, [
                    'species' => 'Tengkawang & anggrek rawa gambut', 'part_used' => 'Biji/umbi', 'utilization_form' => 'Repository / plasma nutfah',
                ]],
                'CLIMATE' => ['Hutan rawa gambut Danau Sentarum', 8.2, 'ton C', 115000, 1200, [
                    'vegetation_type' => 'Rawa gambut', 'biomass_per_ha' => 230, 'carbon_factor' => 0.47,
                ]],
                'EROSION' => ['Tepian Danau Sentarum musim banjir', 3.0, null, 8800000, 55, [
                    'protector_ecosystem' => 'Hutan lereng', 'eroded_area_ha' => 20,
                ]],
                'WATER' => ['Cekungan Danau Sentarum, DAS Kapuas', 2100, 'm³/tahun', 21, 500, [
                    'ecosystem_type' => 'Rawa gambut', 'research_method' => 'Studi hidrologi DAS Kapuas',
                ]],
            ],
            'hpm' => [
                'locations' => ['Semitau', 'Selimbau', 'Suhaid', 'Badau', 'Putussibau', 'Bunut Hilir'],
                'types' => ['Rumah panggung', 'Tanah kavling', 'Rumah tinggal'],
            ],
            'abm' => [
                'risk_type' => 'Banjir musiman merendam permukiman',
                'defensive_action' => 'Tinggikan rumah panggung / relokasi sementara',
                'defensive_goods' => 'Material panggung & perahu evakuasi',
                'locations' => ['Semitau', 'Selimbau', 'Suhaid', 'Badau', 'Bunut Hilir'],
            ],
            'ce' => [
                'scenario_title' => 'Preferensi pengelolaan situs Ramsar Danau Sentarum',
                'attribute_1' => 'Zona larang tangkap ikan', 'levels_1' => ['10% luas danau', '25% luas danau'],
                'attribute_2' => 'Musim larang tangkap', 'levels_2' => ['Tidak ada', 'Musim kemarau'],
                'locations' => ['Semitau', 'Selimbau', 'Suhaid', 'Badau', 'Putussibau', 'Bunut Hilir', 'Sintang', 'Semitau'],
            ],
        ];
    }

    private function bunakenDetail(): array
    {
        return [
            'duv' => [
                ['Rumput laut budidaya', 'Perairan sekitar Bunaken', 18000, 'Kg', 12000, 2000],
                ['Ikan hias tangkapan berkelanjutan', 'Terumbu karang Bunaken', 4200, 'Ekor', 45000, 8000],
            ],
            'ecosystem' => [
                'FOOD' => ['Perairan karang Bunaken', 610, 'kg/ha/tahun', 32000, 190, [
                    'food_type' => 'Perikanan tangkap', 'commodity' => 'Ikan karang berkelanjutan',
                ]],
                'RAWMAT' => ['Kawasan pesisir Bunaken', 15, 'kg/ha/tahun', 18000, 40, [
                    'utilization_type' => 'Kerajinan', 'material_type' => 'Kerang & terumbu mati untuk kerajinan', 'utilization_method' => 'Lainnya',
                ]],
                'GENRES' => ['Terumbu karang Taman Nasional Bunaken', 0.6, 'kg/ha/tahun', 280000, 250, [
                    'species' => 'Biota terumbu karang endemik', 'part_used' => 'Sampel jaringan', 'utilization_form' => 'Riset',
                ]],
                'CLIMATE' => ['Padang lamun sekitar Bunaken', 3.4, 'ton C', 100000, 150, [
                    'vegetation_type' => 'Padang lamun', 'biomass_per_ha' => 60, 'carbon_factor' => 0.34,
                ]],
                'EROSION' => ['Pesisir Pulau Bunaken & Manado Tua', 3.5, null, 16500000, 45, [
                    'protector_ecosystem' => 'Terumbu karang', 'eroded_area_ha' => 25,
                ]],
                'WATER' => ['Daerah tangkapan air Pulau Bunaken', 620, 'm³/tahun', 26, 90, [
                    'ecosystem_type' => 'Hutan lindung', 'research_method' => 'Studi hidrologi pulau kecil',
                ]],
            ],
            'hpm' => [
                'locations' => ['Molas', 'Tongkaina', 'Meras', 'Pulau Bunaken', 'Likupang', 'Manado'],
                'types' => ['Resort/homestay', 'Rumah tinggal', 'Tanah kavling'],
            ],
            'abm' => [
                'risk_type' => 'Penurunan tangkapan akibat kerusakan karang',
                'defensive_action' => 'Melaut lebih jauh / ganti alat tangkap',
                'defensive_goods' => 'Bahan bakar tambahan & alat tangkap',
                'locations' => ['Molas', 'Tongkaina', 'Meras', 'Pulau Bunaken', 'Likupang'],
            ],
            'ce' => [
                'scenario_title' => 'Preferensi pengelolaan kawasan konservasi laut Bunaken',
                'attribute_1' => 'Luas zona inti (larang ambil)', 'levels_1' => ['15% kawasan', '30% kawasan'],
                'attribute_2' => 'Tarif masuk taman laut', 'levels_2' => ['Tetap', 'Naik bertahap'],
                'locations' => ['Molas', 'Tongkaina', 'Meras', 'Pulau Bunaken', 'Likupang', 'Manado', 'Bitung', 'Molas'],
            ],
        ];
    }

    private function kelimutuDetail(): array
    {
        return [
            'duv' => [
                ['Sayuran dataran tinggi (kubis, wortel)', 'Lereng penyangga Kelimutu', 14000, 'Kg', 8500, 1500],
                ['Kayu bakar & kayu bangunan hutan lindung', 'Hutan lindung penyangga Kelimutu', 3100, 'Kg', 4200, 600],
            ],
            'ecosystem' => [
                'FOOD' => ['Lahan pertanian dataran tinggi Moni', 720, 'kg/ha/tahun', 8500, 140, [
                    'food_type' => 'Hortikultura', 'commodity' => 'Sayuran & kopi dataran tinggi',
                ]],
                'RAWMAT' => ['Hutan lindung penyangga Kelimutu', 22, 'kg/ha/tahun', 4200, 85, [
                    'utilization_type' => 'Subsisten / rumah tangga', 'material_type' => 'Kayu bakar & bambu', 'utilization_method' => 'Tebang pilih',
                ]],
                'GENRES' => ['Hutan lindung Taman Nasional Kelimutu', 0.8, 'kg/ha/tahun', 190000, 55, [
                    'species' => 'Edelweis & anggrek hutan pegunungan', 'part_used' => 'Biji/bunga', 'utilization_form' => 'Riset',
                ]],
                'CLIMATE' => ['Hutan pegunungan Taman Nasional Kelimutu', 5.1, 'ton C', 98000, 500, [
                    'vegetation_type' => 'Hutan hujan tropis', 'biomass_per_ha' => 165, 'carbon_factor' => 0.47,
                ]],
                'EROSION' => ['Lereng kaldera Kelimutu rawan longsor', 1.8, null, 8200000, 25, [
                    'protector_ecosystem' => 'Hutan lereng', 'eroded_area_ha' => 15,
                ]],
                'WATER' => ['Daerah tangkapan air kaldera Kelimutu', 890, 'm³/tahun', 23, 160, [
                    'ecosystem_type' => 'Hutan lindung', 'research_method' => 'Studi hidrologi kaldera',
                ]],
            ],
            'hpm' => [
                'locations' => ['Moni', 'Wolowaru', 'Detusoko', 'Ende', 'Koanara', 'Wologai'],
                'types' => ['Homestay', 'Rumah tinggal', 'Tanah kavling'],
            ],
            'abm' => [
                'risk_type' => 'Krisis air bersih musim kemarau dataran tinggi',
                'defensive_action' => 'Beli air tangki / bangun penampungan hujan',
                'defensive_goods' => 'Air tangki & tandon',
                'locations' => ['Moni', 'Wolowaru', 'Detusoko', 'Koanara', 'Wologai'],
            ],
            'ce' => [
                'scenario_title' => 'Preferensi pengelolaan wisata Danau Kelimutu',
                'attribute_1' => 'Kuota pengunjung harian', 'levels_1' => ['500 orang', '1.000 orang'],
                'attribute_2' => 'Retribusi masuk untuk konservasi', 'levels_2' => ['Tetap', 'Naik untuk dana konservasi'],
                'locations' => ['Moni', 'Wolowaru', 'Detusoko', 'Ende', 'Koanara', 'Wologai', 'Maumere', 'Moni'],
            ],
        ];
    }

    // ── shared build logic ──────────────────────────────────────────────

    private function seedDetail(string $code, array $detail): void
    {
        $adminId = 1;
        $project = Project::where('code', $code)->first();
        if (! $project) {
            return;
        }

        $year = (int) ($project->valuationSetting?->base_year ?? 2025);

        foreach ($detail['duv'] as $i => [$goodsType, $location, $qty, $unit, $price, $cost]) {
            $this->createDuv($project->id, $adminId, $i + 1, $goodsType, $location, $qty, $unit, $price, $cost, $year);
        }

        foreach ($detail['ecosystem'] as $serviceKey => [$location, $qty, $qtyUnit, $unitPrice, $areaHa, $extra]) {
            $this->createEcosystemRecord($project->id, $adminId, $serviceKey, $location, $qty, $qtyUnit, $unitPrice, $areaHa, $extra, $year);
        }

        $this->createHpmRows($project->id, $adminId, $detail['hpm']);
        $this->createAbmRows($project->id, $adminId, $detail['abm']);
        $this->createCeRows($project->id, $adminId, $detail['ce']);

        $project->calculateTEV()->save();
    }

    private function createDuv(
        int $projectId, int $adminId, int $seq, string $goodsType, string $location,
        float $quantity, string $unit, float $price, float $cost, int $year,
    ): DuvData {
        $derived = $this->calc->duvRecordValues($quantity, $price, $cost);

        $duv = DuvData::create([
            'project_id' => $projectId,
            'record_code' => sprintf('DUV-%02d', $seq),
            'service_category' => 'provisioning',
            'goods_type' => $goodsType,
            'location' => $location,
            'quantity' => $quantity,
            'unit' => $unit,
            'market_price' => $price,
            'production_cost' => $cost,
            'period_year' => $year,
            'data_source' => 'Survei lapangan',
            'data_status' => 'verified',
            'recorded_by' => $adminId,
            ...$derived,
        ]);

        Benefit::create([
            'project_id' => $projectId,
            'category' => 'direct_use',
            'subcategory' => 'production',
            'description' => $goodsType,
            'value' => $derived['net_value'],
            'period_year' => $year,
            'method_used' => 'DUV',
            'data_source' => 'manual',
            'source_module' => 'DUV',
            'source_record_id' => $duv->id,
            'calculated_by' => $adminId,
        ]);

        return $duv;
    }

    private function createEcosystemRecord(
        int $projectId, int $adminId, string $serviceKey, string $location,
        float $quantity, ?string $quantityUnit, float $unitPrice, float $areaHa,
        array $extra, int $year,
    ): EcosystemServiceRecord {
        $schema = EcosystemServiceSchemas::find($serviceKey);
        $conversion = EcosystemServiceSchemas::priceConversion($serviceKey, $quantityUnit);
        $derived = $this->calc->ecosystemServiceRecordValues($quantity, $unitPrice, $areaHa, $conversion);

        $record = EcosystemServiceRecord::create([
            'project_id' => $projectId,
            'service_key' => $serviceKey,
            'service_category' => $schema['service_category'],
            'record_code' => sprintf('%s-01', $schema['code_prefix']),
            'location' => $location,
            'quantity_value' => $quantity,
            'quantity_unit' => $quantityUnit,
            'unit_price' => $unitPrice,
            'area_ha' => $areaHa,
            'period_year' => $year,
            'data_source' => 'Survei & literatur teknis',
            'extra' => $extra,
            'recorded_by' => $adminId,
            ...$derived,
        ]);

        // Regulating services read as protection/avoided-damage value (indirect
        // use); provisioning services are goods taken directly from the site.
        $isRegulating = $schema['service_category'] === 'regulating';

        Benefit::create([
            'project_id' => $projectId,
            'category' => $isRegulating ? 'indirect_use' : 'direct_use',
            'subcategory' => match ($serviceKey) {
                'CLIMATE' => 'carbon_sequestration',
                'EROSION', 'WATER' => 'water_regulation',
                default => 'production',
            },
            'description' => $schema['name'].' — '.$location,
            'value' => $derived['total_value'],
            'period_year' => $year,
            'method_used' => $schema['valuation_method'] ?? $serviceKey,
            'data_source' => 'manual',
            'source_module' => $serviceKey,
            'source_record_id' => $record->id,
            'calculated_by' => $adminId,
        ]);

        return $record;
    }

    private function createHpmRows(int $projectId, int $adminId, array $cfg): void
    {
        $accessibility = ['Dekat jalan utama', 'Akses jalan desa', 'Dekat jalan utama'];
        $crimeRate = ['Rendah', 'Sedang'];
        $schoolQuality = ['Baik', 'Sedang'];

        foreach (range(1, 6) as $i) {
            HpmData::create([
                'project_id' => $projectId,
                'property_code' => sprintf('HPM-%02d', $i),
                'transaction_price' => rand(120, 480) * 1000000,
                'property_type' => $cfg['types'][($i - 1) % count($cfg['types'])],
                'location' => $cfg['locations'][($i - 1) % count($cfg['locations'])],
                'bedrooms' => rand(2, 4),
                'land_area' => rand(90, 400),
                'building_area' => rand(45, 160),
                'building_age' => rand(1, 20),
                'accessibility' => $accessibility[$i % count($accessibility)],
                'crime_rate' => $crimeRate[$i % count($crimeRate)],
                'school_quality' => $schoolQuality[$i % count($schoolQuality)],
                'air_quality_index' => rand(30, 60),
                'noise_level' => rand(38, 58),
                'distance_green_space' => round(mt_rand(2, 40) / 10, 1),
                'data_source' => 'Survei harga properti lokal',
                'recorded_by' => $adminId,
            ]);
        }
    }

    private function createAbmRows(int $projectId, int $adminId, array $cfg): void
    {
        foreach (range(1, 5) as $i) {
            $quantity = rand(1, 4);
            $unitPrice = rand(80000, 220000);
            $timeCost = rand(10000, 40000);
            $medicalCost = rand(0, 150000);
            $sickDays = rand(0, 3);
            $dailyWage = rand(60000, 120000);

            $derived = $this->calc->abmRecordValues($quantity, $unitPrice, $timeCost, $medicalCost, $sickDays, $dailyWage);

            AbmData::create([
                'project_id' => $projectId,
                'respondent_code' => sprintf('ABM-%02d', $i),
                'location' => $cfg['locations'][($i - 1) % count($cfg['locations'])],
                'risk_type' => $cfg['risk_type'],
                'exposure_condition' => 'Terpapar langsung, musiman',
                'defensive_action' => $cfg['defensive_action'],
                'defensive_goods' => $cfg['defensive_goods'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'time_cost' => $timeCost,
                'medical_cost' => $medicalCost,
                'sick_days' => $sickDays,
                'daily_wage' => $dailyWage,
                'household_size' => rand(3, 6),
                'affected_population' => rand(600, 2000),
                'data_source' => 'Survei rumah tangga',
                'recorded_by' => $adminId,
                ...$derived,
            ]);
        }
    }

    private function createCeRows(int $projectId, int $adminId, array $cfg): void
    {
        $choices = ['a', 'b', 'status_quo'];

        foreach (range(1, 8) as $i) {
            CeData::create([
                'project_id' => $projectId,
                'respondent_code' => sprintf('CE-%02d', $i),
                'location' => $cfg['locations'][$i - 1],
                'age' => rand(20, 60),
                'education' => ['SMA', 'D3', 'S1'][($i - 1) % 3],
                'income' => rand(2500000, 12000000),
                'scenario_title' => $cfg['scenario_title'],
                'choice_set' => 'Set-1',
                'alternative_a' => 'Paket A',
                'alternative_b' => 'Paket B',
                'status_quo' => 'Kondisi saat ini',
                'chosen_alternative' => $choices[$i % 3 === 0 ? 2 : ($i % 2)],
                'attribute_1' => $cfg['attribute_1'],
                'attribute_1_level' => $cfg['levels_1'][$i % 2],
                'attribute_2' => $cfg['attribute_2'],
                'attribute_2_level' => $cfg['levels_2'][$i % 2],
                'cost_attribute' => rand(15, 45) * 1000,
                'data_source' => 'Survei choice experiment',
                'recorded_by' => $adminId,
            ]);
        }
    }
}
