<?php

namespace Database\Seeders;

use App\Models\Benefit;
use App\Models\Cost;
use App\Models\CvmData;
use App\Models\EopData;
use App\Models\EnvironmentalCoefficient;
use App\Models\MarketPrice;
use App\Models\Project;
use App\Models\TcmData;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = 1;

        // === Market Prices ===
        $prices = [
            ['commodity_name' => 'Padi',         'unit' => 'Ton', 'price' => 5500000,   'year' => 2025, 'source' => 'BPS 2025'],
            ['commodity_name' => 'Ikan Nila',     'unit' => 'Kg',  'price' => 35000,     'year' => 2025, 'source' => 'Kementan 2025'],
            ['commodity_name' => 'Kayu Jati',     'unit' => 'm³',  'price' => 8500000,   'year' => 2025, 'source' => 'Perhutani 2025'],
            ['commodity_name' => 'Karet',         'unit' => 'Ton', 'price' => 18000000,  'year' => 2025, 'source' => 'BPS 2025'],
            ['commodity_name' => 'Kelapa Sawit',  'unit' => 'Ton', 'price' => 2200000,   'year' => 2025, 'source' => 'BPS 2025'],
        ];
        foreach ($prices as $p) {
            MarketPrice::create([...$p, 'approved_by' => $adminId]);
        }

        // === Environmental Coefficients ===
        // ENUM allowed: carbon_sequestration, water_retention, biodiversity, other
        $coefficients = [
            [
                'name' => 'Serapan Karbon Hutan',
                'code' => 'CARB-01',
                'value' => 6.5,
                'unit' => 'ton CO2/ha/tahun',
                'type' => 'carbon_sequestration',   // ← fixed
                'source' => 'IPCC 2021',
                'year' => 2021,
            ],
            [
                'name' => 'Regulasi Air DAS',
                'code' => 'WATR-01',
                'value' => 22.8,
                'unit' => 'm3/ha/tahun',
                'type' => 'water_retention',          // ← fixed
                'source' => 'Kementerian LHK',
                'year' => 2023,
            ],
            [
                'name' => 'Allometrik Biomassa',
                'code' => 'BIOM-01',
                'value' => 0.0509,
                'unit' => 'ton/m3',
                'type' => 'other',                    // ← fixed (no 'biomass' enum)
                'source' => 'Brown 1997',
                'year' => 1997,
            ],
        ];
        foreach ($coefficients as $c) {
            EnvironmentalCoefficient::create([...$c, 'approved_by' => $adminId, 'description' => 'Koefisien standar']);
        }

        // === Project 1: Taman Nasional Ujung Kulon ===
        $p1 = Project::create([
            'code'        => 'PROJ-001',
            'name'        => 'Valuasi Ekonomi Taman Nasional Ujung Kulon',
            'description' => 'Penilaian Total Economic Value (TEV) dari Taman Nasional Ujung Kulon meliputi manfaat wisata, regulasi air, dan nilai keberadaan badak jawa.',
            'location'    => 'Pandeglang, Banten',
            'province'    => 'Banten',
            'latitude'    => -6.7516,
            'longitude'   => 105.3297,
            'status'      => 'published',
            'created_by'  => $adminId,
        ]);

        // EOP Data for P1
        EopData::create(['project_id' => $p1->id, 'recorded_by' => $adminId, 'commodity_name' => 'Ikan Laut',  'production_before' => 12000, 'production_after' => 10500, 'unit' => 'Ton', 'market_price' => 25000000, 'impact_type' => 'negative']);
        EopData::create(['project_id' => $p1->id, 'recorded_by' => $adminId, 'commodity_name' => 'Madu Hutan', 'production_before' => 500,   'production_after' => 650,   'unit' => 'Kg',  'market_price' => 120000,   'impact_type' => 'positive']);

        // TCM Data for P1 — respondent_id is INTEGER (auto increment-style, unique)
        $tcmOrigins = ['Jakarta', 'Bandung', 'Serang', 'Bogor', 'Tangerang', 'Sukabumi', 'Cilegon', 'Lampung'];
        foreach (range(1, 8) as $i) {
            TcmData::create([
                'project_id'         => $p1->id,
                'recorded_by'        => $adminId,
                'respondent_id'      => 1000 + $i,                          // ← integer
                'distance'           => rand(20, 350),
                'transportation_cost'=> rand(50000, 800000),
                'time_cost'          => rand(20000, 200000),
                'visit_frequency'    => rand(1, 5),
                'origin_location'    => $tcmOrigins[$i - 1],
                'respondent_category'=> $i <= 5 ? 'Wisatawan' : 'Lokal',
            ]);
        }

        // CVM Data for P1 — respondent_id is INTEGER
        $cvmLocations = ['Jakarta Selatan','Jakarta Timur','Bogor','Depok','Tangerang','Serang','Bandung','Cirebon','Sukabumi','Pandeglang'];
        $eduLevels    = ['SMA','S1','S2','D3','S1'];
        foreach (range(1, 10) as $i) {
            $willing = $i <= 7;
            CvmData::create([
                'project_id'         => $p1->id,
                'recorded_by'        => $adminId,
                'respondent_id'      => 2000 + $i,                          // ← integer
                'willing_to_pay'     => $willing,
                'wtp'                => $willing ? rand(25000, 200000) : 0,
                'reason_if_unwilling'=> !$willing ? 'Sudah cukup membayar pajak' : null,
                'household_size'     => rand(2, 6),
                'household_income'   => rand(3000000, 15000000),
                'education_level'    => $eduLevels[$i % 5],
                'respondent_location'=> $cvmLocations[$i - 1],
            ]);
        }

        // Benefits for P1
        $this->seedBenefits($p1->id, $adminId, [
            ['direct_use',   'tourism',              'Manfaat wisata dari pengunjung TNUK',      45000000000, 'TCM', 'tcm'],
            ['direct_use',   'production',           'Hasil hutan non-kayu (madu, rotan)',       12500000000, 'EOP', 'eop'],
            ['indirect_use', 'water_regulation',     'Regulasi air DAS Ujung Kulon',             28000000000, 'RC',  'manual'],
            ['indirect_use', 'carbon_sequestration', 'Penyerapan karbon hutan primer',           35000000000, 'EOP', 'manual'],
            ['non_use',      'existence_value',      'Nilai keberadaan Badak Jawa',              52000000000, 'CVM', 'cvm'],
        ]);

        // Costs for P1
        $this->seedCosts($p1->id, $adminId, [
            ['direct_cost',   'operation_maintenance', 'Biaya operasional TNUK per tahun',   15000000000, 'Tahunan'],
            ['direct_cost',   'investment',            'Investasi infrastruktur wisata',       8000000000, 'Investasi Awal'],
            ['indirect_cost', 'opportunity_cost',      'Opportunity cost lahan konservasi',  22000000000, 'Tahunan'],
        ]);

        $p1->calculateTEV()->save();

        // === Project 2: Danau Toba ===
        $p2 = Project::create([
            'code'        => 'PROJ-002',
            'name'        => 'Valuasi Ekonomi Kawasan Danau Toba',
            'description' => 'Penilaian TEV Kawasan Strategis Pariwisata Nasional Danau Toba termasuk nilai wisata, perikanan, dan keberadaan budaya Batak.',
            'location'    => 'Sumatera Utara',
            'province'    => 'Sumatera Utara',
            'latitude'    => 2.6845,
            'longitude'   => 98.8588,
            'status'      => 'published',
            'created_by'  => $adminId,
        ]);

        $this->seedBenefits($p2->id, $adminId, [
            ['direct_use',   'tourism',          'Wisata Danau Toba',               85000000000, 'TCM', 'tcm'],
            ['direct_use',   'production',       'Perikanan air tawar',             22000000000, 'EOP', 'eop'],
            ['indirect_use', 'water_regulation', 'Suplai air bersih regional',      42000000000, 'RC',  'manual'],
            ['non_use',      'existence_value',  'Nilai keberadaan budaya Batak',   38000000000, 'CVM', 'cvm'],
        ]);

        $this->seedCosts($p2->id, $adminId, [
            ['direct_cost', 'investment',            'Pembangunan infrastruktur KSPN', 45000000000, 'Investasi Awal'],
            ['direct_cost', 'operation_maintenance', 'Operasional tahunan',            12000000000, 'Tahunan'],
        ]);

        $p2->calculateTEV()->save();

        // === Project 3: Mangrove Delta Citarum (In Progress) ===
        $p3 = Project::create([
            'code'        => 'PROJ-003',
            'name'        => 'Valuasi Mangrove Delta Citarum',
            'description' => 'Studi valuasi mangrove di muara Sungai Citarum meliputi perlindungan pantai, nursery ground, dan serapan karbon biru.',
            'location'    => 'Karawang, Jawa Barat',
            'province'    => 'Jawa Barat',
            'latitude'    => -5.9395,
            'longitude'   => 107.3050,
            'status'      => 'in_progress',
            'created_by'  => $adminId,
        ]);

        EopData::create(['project_id' => $p3->id, 'recorded_by' => $adminId, 'commodity_name' => 'Udang Windu', 'production_before' => 800, 'production_after' => 1200, 'unit' => 'Ton', 'market_price' => 85000000, 'impact_type' => 'positive']);

        $this->seedBenefits($p3->id, $adminId, [
            ['direct_use',   'production',           'Perikanan tambak',       18000000000, 'EOP', 'eop'],
            ['indirect_use', 'carbon_sequestration', 'Blue carbon mangrove',   15000000000, 'EOP', 'manual'],
        ]);

        $this->seedCosts($p3->id, $adminId, [
            ['direct_cost', 'investment', 'Rehabilitasi mangrove', 5000000000, 'Investasi'],
        ]);

        $p3->calculateTEV()->save();

        // === Project 4: Raja Ampat (Published) ===
        $p4 = Project::create([
            'code'        => 'PROJ-004',
            'name'        => 'Valuasi Ekonomi Ekosistem Terumbu Karang Raja Ampat',
            'description' => 'Penilaian TEV ekosistem terumbu karang Raja Ampat termasuk wisata bahari, perikanan berkelanjutan, dan pelindungan pantai.',
            'location'    => 'Raja Ampat, Papua Barat',
            'province'    => 'Papua Barat Daya',
            'latitude'    => -0.2348,
            'longitude'   => 130.5167,
            'status'      => 'published',
            'created_by'  => $adminId,
        ]);

        $this->seedBenefits($p4->id, $adminId, [
            ['direct_use',   'tourism',              'Wisata bahari diving & snorkeling', 120000000000, 'TCM', 'tcm'],
            ['direct_use',   'production',           'Perikanan berkelanjutan',            30000000000, 'EOP', 'eop'],
            ['indirect_use', 'water_regulation',     'Perlindungan pantai dari erosi',     55000000000, 'RC',  'manual'],
            ['non_use',      'existence_value',      'Keanekaragaman hayati laut',         75000000000, 'CVM', 'cvm'],
            ['non_use',      'bequest_value',        'Warisan untuk generasi masa depan',  40000000000, 'CVM', 'cvm'],
        ]);

        $this->seedCosts($p4->id, $adminId, [
            ['direct_cost',   'operation_maintenance', 'Patroli & operasional MPA',      20000000000, 'Tahunan'],
            ['direct_cost',   'investment',            'Infrastruktur ekowisata',         35000000000, 'Investasi Awal'],
            ['indirect_cost', 'opportunity_cost',      'Pembatasan penangkapan ikan',     18000000000, 'Tahunan'],
        ]);

        $p4->calculateTEV()->save();
    }

    // ── helpers ──────────────────────────────────────────────────────────
    private function seedBenefits(int $projectId, int $adminId, array $rows): void
    {
        foreach ($rows as [$category, $subcategory, $description, $value, $method, $source]) {
            Benefit::create([
                'project_id'    => $projectId,
                'category'      => $category,
                'subcategory'   => $subcategory,
                'description'   => $description,
                'value'         => $value,
                'method_used'   => $method,
                'data_source'   => $source,
                'calculated_by' => $adminId,
            ]);
        }
    }

    private function seedCosts(int $projectId, int $adminId, array $rows): void
    {
        foreach ($rows as [$category, $subcategory, $description, $value, $paymentType]) {
            Cost::create([
                'project_id'    => $projectId,
                'category'      => $category,
                'subcategory'   => $subcategory,
                'description'   => $description,
                'value'         => $value,
                'payment_type'  => $paymentType,
                'calculated_by' => $adminId,
            ]);
        }
    }
}
