<?php

namespace Database\Seeders;

use App\Models\Benefit;
use App\Models\Cost;
use App\Models\CvmData;
use App\Models\EopData;
use App\Models\Project;
use App\Models\ProjectValuationSetting;
use App\Models\TcmData;
use App\Services\Valuation\EconomicValuationCalculator;
use Illuminate\Database\Seeder;

/**
 * Six illustrative published projects, one per island group (Jawa, Sumatera,
 * Kalimantan, Sulawesi, Nusa Tenggara, plus a second Jawa site for the coastal
 * method mix), so the admin UI — module status tiles, benefit/cost tables,
 * present-value rollups — has enough varied data to demo or visually check
 * without touching the seeders under test (SampleDataSeeder,
 * RegionalValuationSeeder, PulauObiEcosystemSeeder).
 *
 * Codes are prefixed DEMO- rather than PROJ- specifically so this batch is
 * easy to find and delete later: `Project::where('code', 'like',
 * 'DEMO-%')->get()->each->delete()`.
 *
 * Every benefit/cost is dated at the project's own base_year, so its present
 * value equals its nominal value (discount factor = 1) — the figures shown
 * on screen are exactly the ones written here, nothing hidden in a discount
 * calculation.
 */
class DemoRegionsSeeder extends Seeder
{
    private EconomicValuationCalculator $calc;

    public function run(): void
    {
        $this->calc = new EconomicValuationCalculator;
        $adminId = 1;

        $this->seedRegion($this->karstGunungSewuConfig($adminId));
        $this->seedRegion($this->wayKambasConfig($adminId));
        $this->seedRegion($this->segaraAnakanConfig($adminId));
        $this->seedRegion($this->danauSentarumConfig($adminId));
        $this->seedRegion($this->bunakenConfig($adminId));
        $this->seedRegion($this->kelimutuConfig($adminId));
    }

    // ── region configs ──────────────────────────────────────────────────

    private function karstGunungSewuConfig(int $adminId): array
    {
        return [
            'project' => [
                'code' => 'DEMO-001',
                'name' => 'Valuasi Ekonomi Kawasan Karst Gunung Sewu',
                'description' => 'Penilaian TEV kawasan karst Gunung Sewu meliputi wisata gua, pertanian lahan karst, dan regulasi air tanah.',
                'location' => 'Gunungkidul, Daerah Istimewa Yogyakarta',
                'province' => 'Daerah Istimewa Yogyakarta',
                'latitude' => -8.0300,
                'longitude' => 110.6021,
                'status' => 'published',
            ],
            'baseYear' => 2025,
            'eop' => ['commodity' => 'Jagung Lahan Karst', 'before' => 8000, 'after' => 9500, 'unit' => 'Ton', 'price' => 4500000, 'impact' => 'positive'],
            'tcm' => ['count' => 8, 'origins' => ['Yogyakarta', 'Wonosari', 'Solo', 'Klaten', 'Sleman', 'Bantul', 'Semarang', 'Purworejo']],
            'cvm' => ['count' => 10, 'locations' => ['Yogyakarta', 'Wonosari', 'Playen', 'Semanu', 'Tanjungsari', 'Solo', 'Klaten', 'Sleman', 'Bantul', 'Purworejo']],
            'benefits' => [
                ['direct_use', 'tourism', 'Wisata susur gua dan geosite karst', 32000000000, 'TCM', 'tcm'],
                ['direct_use', 'production', 'Pertanian lahan karst (jagung, ketela)', 9500000000, 'EOP', 'eop'],
                ['indirect_use', 'water_regulation', 'Regulasi air tanah sistem akuifer karst', 24000000000, 'RC', 'manual'],
                ['existence_value', 'existence_value', 'Nilai keberadaan bentang alam karst', 21000000000, 'CVM', 'cvm'],
            ],
            'costs' => [
                ['direct_cost', 'investment', 'Infrastruktur wisata gua (jalur, penerangan)', 6000000000, 'Investasi Awal'],
                ['direct_cost', 'operation_maintenance', 'Operasional & pemandu wisata tahunan', 3200000000, 'Tahunan'],
            ],
        ];
    }

    private function wayKambasConfig(int $adminId): array
    {
        return [
            'project' => [
                'code' => 'DEMO-002',
                'name' => 'Valuasi Ekonomi Taman Nasional Way Kambas',
                'description' => 'Penilaian TEV Taman Nasional Way Kambas meliputi ekowisata gajah, nilai keberadaan gajah Sumatera, dan perikanan rawa.',
                'location' => 'Lampung Timur, Lampung',
                'province' => 'Lampung',
                'latitude' => -4.9375,
                'longitude' => 105.7550,
                'status' => 'published',
            ],
            'baseYear' => 2025,
            'eop' => ['commodity' => 'Ikan Rawa', 'before' => 1400, 'after' => 1150, 'unit' => 'Ton', 'price' => 22000000, 'impact' => 'negative'],
            'tcm' => ['count' => 8, 'origins' => ['Bandar Lampung', 'Metro', 'Way Jepara', 'Lampung Timur', 'Palembang', 'Jakarta', 'Bandar Lampung', 'Kotabumi']],
            'cvm' => ['count' => 10, 'locations' => ['Bandar Lampung', 'Metro', 'Way Jepara', 'Lampung Timur', 'Labuhan Ratu', 'Palembang', 'Jakarta', 'Kotabumi', 'Bandar Lampung', 'Metro']],
            'benefits' => [
                ['direct_use', 'tourism', 'Ekowisata safari & pusat konservasi gajah', 38000000000, 'TCM', 'tcm'],
                ['direct_use', 'production', 'Perikanan rawa sekitar taman nasional', 6500000000, 'EOP', 'eop'],
                ['indirect_use', 'carbon_sequestration', 'Serapan karbon hutan rawa gambut', 41000000000, 'RC', 'manual'],
                ['existence_value', 'existence_value', 'Nilai keberadaan Gajah Sumatera', 58000000000, 'CVM', 'cvm'],
                ['bequest_value', 'bequest_value', 'Warisan konservasi untuk generasi mendatang', 26000000000, 'CVM', 'manual'],
            ],
            'costs' => [
                ['direct_cost', 'operation_maintenance', 'Patroli anti-perburuan & operasional', 17000000000, 'Tahunan'],
                ['direct_cost', 'investment', 'Pusat konservasi & rehabilitasi gajah', 12000000000, 'Investasi Awal'],
                ['indirect_cost', 'opportunity_cost', 'Pembatasan perluasan lahan pertanian', 9000000000, 'Tahunan'],
            ],
        ];
    }

    private function segaraAnakanConfig(int $adminId): array
    {
        return [
            'project' => [
                'code' => 'DEMO-003',
                'name' => 'Valuasi Ekonomi Mangrove Segara Anakan',
                'description' => 'Penilaian TEV kawasan mangrove Segara Anakan meliputi perikanan tambak, perlindungan pantai, dan serapan karbon biru.',
                'location' => 'Cilacap, Jawa Tengah',
                'province' => 'Jawa Tengah',
                'latitude' => -7.7011,
                'longitude' => 108.9647,
                'status' => 'published',
            ],
            'baseYear' => 2025,
            'eop' => ['commodity' => 'Udang & Kepiting Bakau', 'before' => 900, 'after' => 1300, 'unit' => 'Ton', 'price' => 95000000, 'impact' => 'positive'],
            'cvm' => ['count' => 10, 'locations' => ['Cilacap', 'Kroya', 'Majenang', 'Sidareja', 'Kawunganten', 'Cilacap', 'Kroya', 'Jeruklegi', 'Cilacap', 'Adipala']],
            'benefits' => [
                ['direct_use', 'production', 'Perikanan tambak udang & kepiting bakau', 19500000000, 'EOP', 'eop'],
                ['indirect_use', 'carbon_sequestration', 'Blue carbon ekosistem mangrove', 27000000000, 'RC', 'manual'],
                ['indirect_use', 'water_regulation', 'Perlindungan pantai dari abrasi & rob', 33000000000, 'RC', 'manual'],
                ['existence_value', 'existence_value', 'Nilai keberadaan ekosistem mangrove', 16000000000, 'CVM', 'cvm'],
            ],
            'costs' => [
                ['direct_cost', 'investment', 'Rehabilitasi & penanaman mangrove', 7500000000, 'Investasi'],
                ['direct_cost', 'operation_maintenance', 'Pengawasan kawasan tambak & mangrove', 4100000000, 'Tahunan'],
            ],
        ];
    }

    private function danauSentarumConfig(int $adminId): array
    {
        return [
            'project' => [
                'code' => 'DEMO-004',
                'name' => 'Valuasi Ekonomi Taman Nasional Danau Sentarum',
                'description' => 'Penilaian TEV Taman Nasional Danau Sentarum meliputi perikanan air tawar, wisata lahan basah, dan regulasi banjir.',
                'location' => 'Kapuas Hulu, Kalimantan Barat',
                'province' => 'Kalimantan Barat',
                'latitude' => 0.8206,
                'longitude' => 111.9328,
                'status' => 'published',
            ],
            'baseYear' => 2025,
            'eop' => ['commodity' => 'Ikan Air Tawar Danau', 'before' => 3000, 'after' => 3400, 'unit' => 'Ton', 'price' => 28000000, 'impact' => 'positive'],
            'tcm' => ['count' => 8, 'origins' => ['Pontianak', 'Putussibau', 'Sintang', 'Semitau', 'Pontianak', 'Sanggau', 'Putussibau', 'Kapuas Hulu']],
            'cvm' => ['count' => 10, 'locations' => ['Pontianak', 'Putussibau', 'Sintang', 'Semitau', 'Selimbau', 'Pontianak', 'Sanggau', 'Putussibau', 'Kapuas Hulu', 'Sintang']],
            'benefits' => [
                ['direct_use', 'production', 'Perikanan air tawar berkelanjutan', 24000000000, 'EOP', 'eop'],
                ['direct_use', 'tourism', 'Ekowisata lahan basah & pengamatan burung', 14000000000, 'TCM', 'tcm'],
                ['indirect_use', 'water_regulation', 'Regulasi banjir DAS Kapuas', 46000000000, 'RC', 'manual'],
                ['existence_value', 'existence_value', 'Nilai keberadaan situs Ramsar lahan basah', 29000000000, 'CVM', 'cvm'],
            ],
            'costs' => [
                ['direct_cost', 'operation_maintenance', 'Pengawasan & operasional taman nasional', 11000000000, 'Tahunan'],
                ['indirect_cost', 'opportunity_cost', 'Pembatasan konversi lahan gambut', 15000000000, 'Tahunan'],
            ],
        ];
    }

    private function bunakenConfig(int $adminId): array
    {
        return [
            'project' => [
                'code' => 'DEMO-005',
                'name' => 'Valuasi Ekonomi Taman Nasional Bunaken',
                'description' => 'Penilaian TEV Taman Nasional Bunaken meliputi wisata selam, perikanan karang berkelanjutan, dan perlindungan pantai.',
                'location' => 'Manado, Sulawesi Utara',
                'province' => 'Sulawesi Utara',
                'latitude' => 1.6217,
                'longitude' => 124.7550,
                'status' => 'published',
            ],
            'baseYear' => 2025,
            'eop' => ['commodity' => 'Perikanan Karang', 'before' => 1800, 'after' => 2100, 'unit' => 'Ton', 'price' => 32000000, 'impact' => 'positive'],
            'tcm' => ['count' => 8, 'origins' => ['Manado', 'Bitung', 'Tomohon', 'Minahasa', 'Jakarta', 'Manado', 'Makassar', 'Manado']],
            'cvm' => ['count' => 10, 'locations' => ['Manado', 'Bitung', 'Tomohon', 'Minahasa', 'Likupang', 'Manado', 'Bitung', 'Airmadidi', 'Manado', 'Tondano']],
            'benefits' => [
                ['direct_use', 'tourism', 'Wisata selam & snorkeling terumbu karang', 95000000000, 'TCM', 'tcm'],
                ['direct_use', 'production', 'Perikanan karang berkelanjutan', 15500000000, 'EOP', 'eop'],
                ['indirect_use', 'water_regulation', 'Perlindungan pantai dari gelombang', 37000000000, 'RC', 'manual'],
                ['existence_value', 'existence_value', 'Keanekaragaman hayati terumbu karang', 62000000000, 'CVM', 'cvm'],
            ],
            'costs' => [
                ['direct_cost', 'operation_maintenance', 'Patroli & operasional kawasan konservasi laut', 18000000000, 'Tahunan'],
                ['direct_cost', 'investment', 'Infrastruktur dermaga & pusat selam', 22000000000, 'Investasi Awal'],
                ['indirect_cost', 'opportunity_cost', 'Pembatasan penangkapan ikan di zona inti', 10500000000, 'Tahunan'],
            ],
        ];
    }

    private function kelimutuConfig(int $adminId): array
    {
        return [
            'project' => [
                'code' => 'DEMO-006',
                'name' => 'Valuasi Ekonomi Taman Nasional Kelimutu',
                'description' => 'Penilaian TEV Taman Nasional Kelimutu meliputi wisata danau kawah tiga warna, nilai keberadaan, dan kopi arabika highland.',
                'location' => 'Ende, Nusa Tenggara Timur',
                'province' => 'Nusa Tenggara Timur',
                'latitude' => -8.7690,
                'longitude' => 121.8180,
                'status' => 'published',
            ],
            'baseYear' => 2025,
            'eop' => ['commodity' => 'Kopi Arabika Flores', 'before' => 380, 'after' => 520, 'unit' => 'Ton', 'price' => 75000000, 'impact' => 'positive'],
            'tcm' => ['count' => 8, 'origins' => ['Ende', 'Maumere', 'Bajawa', 'Kupang', 'Jakarta', 'Ende', 'Ruteng', 'Ende']],
            'cvm' => ['count' => 10, 'locations' => ['Ende', 'Maumere', 'Bajawa', 'Kupang', 'Moni', 'Ende', 'Ruteng', 'Detusoko', 'Ende', 'Wolowaru']],
            'benefits' => [
                ['direct_use', 'tourism', 'Wisata Danau Kelimutu tiga warna', 41000000000, 'TCM', 'tcm'],
                ['direct_use', 'production', 'Kopi arabika highland penyangga taman nasional', 11500000000, 'EOP', 'eop'],
                ['existence_value', 'existence_value', 'Nilai keberadaan fenomena danau tiga warna', 34000000000, 'CVM', 'cvm'],
                ['bequest_value', 'bequest_value', 'Warisan alam & budaya untuk generasi mendatang', 18000000000, 'CVM', 'manual'],
            ],
            'costs' => [
                ['direct_cost', 'operation_maintenance', 'Operasional & pemeliharaan jalur wisata', 5200000000, 'Tahunan'],
                ['direct_cost', 'investment', 'Infrastruktur pengamatan & keselamatan pengunjung', 4800000000, 'Investasi Awal'],
            ],
        ];
    }

    // ── shared build logic ──────────────────────────────────────────────

    private function seedRegion(array $config): void
    {
        $adminId = 1;
        $p = Project::create([...$config['project'], 'created_by' => $adminId]);

        ProjectValuationSetting::create([
            'project_id' => $p->id,
            'base_year' => $config['baseYear'],
            'discount_rate' => 6.00,
            'analysis_period' => 10,
            'currency' => 'IDR',
            'eop_value_basis' => 'net',
            'updated_by' => $adminId,
        ]);

        if (isset($config['eop'])) {
            $this->createEop($p->id, $adminId, $config['eop'], $config['baseYear']);
        }

        if (isset($config['tcm'])) {
            foreach (range(1, $config['tcm']['count']) as $i) {
                TcmData::create([
                    'project_id' => $p->id,
                    'recorded_by' => $adminId,
                    'respondent_id' => ($p->id * 1000) + $i,
                    'distance' => rand(15, 400),
                    'transportation_cost' => rand(50000, 900000),
                    'time_cost' => rand(20000, 220000),
                    'visit_frequency' => rand(1, 5),
                    'origin_location' => $config['tcm']['origins'][$i - 1],
                    'respondent_category' => $i <= (int) ceil($config['tcm']['count'] / 2) ? 'Wisatawan' : 'Lokal',
                ]);
            }
        }

        if (isset($config['cvm'])) {
            $eduLevels = ['SMA', 'S1', 'S2', 'D3', 'S1'];
            foreach (range(1, $config['cvm']['count']) as $i) {
                $willing = $i <= (int) ceil($config['cvm']['count'] * 0.7);
                CvmData::create([
                    'project_id' => $p->id,
                    'recorded_by' => $adminId,
                    'respondent_id' => ($p->id * 2000) + $i,
                    'willing_to_pay' => $willing,
                    'wtp' => $willing ? rand(25000, 200000) : 0,
                    'reason_if_unwilling' => ! $willing ? 'Sudah cukup membayar pajak' : null,
                    'household_size' => rand(2, 6),
                    'household_income' => rand(3000000, 15000000),
                    'education_level' => $eduLevels[$i % 5],
                    'respondent_location' => $config['cvm']['locations'][$i - 1],
                ]);
            }
        }

        $this->seedBenefits($p->id, $adminId, $config['benefits'], $config['baseYear']);
        $this->seedCosts($p->id, $adminId, $config['costs'], $config['baseYear']);

        $p->calculateTEV()->save();
    }

    private function createEop(int $projectId, int $adminId, array $eop, int $year): EopData
    {
        $derived = $this->calc->eopRecordValues($eop['before'], $eop['after'], $eop['price']);
        unset($derived['impact_direction']);

        return EopData::create([
            'project_id' => $projectId,
            'recorded_by' => $adminId,
            'commodity_name' => $eop['commodity'],
            'production_before' => $eop['before'],
            'production_after' => $eop['after'],
            'unit' => $eop['unit'],
            'market_price' => $eop['price'],
            'impact_type' => $eop['impact'],
            'period_year' => $year,
            ...$derived,
        ]);
    }

    private function seedBenefits(int $projectId, int $adminId, array $rows, int $year): void
    {
        foreach ($rows as [$category, $subcategory, $description, $value, $method, $source]) {
            Benefit::create([
                'project_id' => $projectId,
                'category' => $category,
                'subcategory' => $subcategory,
                'description' => $description,
                'value' => $value,
                'period_year' => $year,
                'method_used' => $method,
                'data_source' => $source,
                'calculated_by' => $adminId,
            ]);
        }
    }

    private function seedCosts(int $projectId, int $adminId, array $rows, int $year): void
    {
        foreach ($rows as [$category, $subcategory, $description, $value, $paymentType]) {
            Cost::create([
                'project_id' => $projectId,
                'category' => $category,
                'subcategory' => $subcategory,
                'description' => $description,
                'value' => $value,
                'year_applied' => $year,
                'payment_type' => $paymentType,
                'calculated_by' => $adminId,
            ]);
        }
    }
}
