<?php

namespace Database\Seeders;

use App\Models\Benefit;
use App\Models\Cost;
use App\Models\CvmData;
use App\Models\EopData;
use App\Models\Project;
use App\Models\TcmData;
use App\Services\Valuation\EconomicValuationCalculator;
use Illuminate\Database\Seeder;

/**
 * Adds three published projects — Nusa Penida (Bali), Pegunungan Meratus /
 * Loksado (Kalimantan Selatan), and the northern part of Wangi-Wangi Island
 * (Sulawesi Tenggara, NOT the whole Wakatobi National Park) — each scoped to
 * a real, named place with a hand-traced survey-area polygon (not a generic
 * circle), and with Benefit values actually computed via
 * EconomicValuationCalculator (TCM, CVM open-ended, EOP) from
 * synthetic-but-realistic respondent data.
 *
 * It also backfills `boundary_geojson`, location, and coordinates for the
 * two published SampleDataSeeder projects that didn't get a proper
 * survey-area polygon yet (Ujung Kulon, Raja Ampat), plus Danau Toba for
 * consistency with its existing local fallback file.
 *
 * All coordinates are anchored to real, verifiable places (village/harbor/
 * airport centers) rather than arbitrary points — see the inline notes on
 * each polygon for the landmarks used. This is still illustrative "dummy"
 * survey data, not an authoritative land-use boundary.
 *
 * Re-running this seeder is safe: existing rows for these specific projects
 * are replaced (force-deleted, not just skipped), so it can be used to
 * refresh the demo dataset.
 */
class RegionalValuationSeeder extends Seeder
{
    private EconomicValuationCalculator $calc;

    public function run(): void
    {
        $this->calc = new EconomicValuationCalculator;
        $adminId = 1;

        $this->enrichExistingProjects();

        $this->seedRegion($this->baliConfig($adminId));
        $this->seedRegion($this->kalimantanSelatanConfig($adminId));
        $this->seedRegion($this->sulawesiTenggaraConfig($adminId));
    }

    /**
     * PROJ-001 (Ujung Kulon) and PROJ-004 (Raja Ampat) were seeded by
     * SampleDataSeeder with a single lat/lng point and no polygon. Anchor
     * them to a real named place (their park entrance / dive-hub village)
     * and trace a peninsula/strait-shaped boundary instead of a marker.
     * PROJ-002 (Danau Toba) only has a *local* fallback file on the
     * frontend — mirror it into the DB too. `update()` is idempotent.
     */
    private function enrichExistingProjects(): void
    {
        // Anchored at Taman Jaya (-6.78316, 105.503841), the park's main
        // entrance village, with the peninsula traced west to Tanjung Layar
        // / "Java Head" (-6.74704, 105.214022), its westernmost tip.
        Project::where('code', 'PROJ-001')->update([
            'location' => 'Taman Jaya, Pandeglang, Banten',
            'latitude' => -6.7832,
            'longitude' => 105.5038,
            'boundary_geojson' => $this->polygonFeature(
                [
                    [105.512, -6.758], [105.470, -6.750], [105.420, -6.745], [105.370, -6.742],
                    [105.320, -6.744], [105.270, -6.748], [105.230, -6.748], [105.214, -6.747],
                    [105.235, -6.760], [105.280, -6.775], [105.330, -6.785], [105.390, -6.792],
                    [105.450, -6.795], [105.512, -6.786], [105.512, -6.758],
                ],
                'Semenanjung Ujung Kulon',
                'Area survey valuasi ekonomi Semenanjung Ujung Kulon, dari pintu masuk Taman Jaya hingga Tanjung Layar (Java Head), Banten.'
            ),
        ]);

        // Anchored on Pulau Mansuar (-0.5902, 130.5964) — verified via OSM/
        // Nominatim geocoding, since the Dampier Strait's Wikipedia point
        // (00°40'S 130°40'E) actually falls in open water south of this
        // island cluster. The ring is sized to also contain Arborek
        // (-0.5654, 130.5165, west) and Kri (-0.5673, 130.6586, Mansuar's
        // eastern tip) — the most-visited core of Raja Ampat, not the whole
        // ~40,000 km2 regency.
        Project::where('code', 'PROJ-004')->update([
            'location' => 'Selat Dampier (P. Mansuar–Kri–Arborek), Waigeo Selatan, Raja Ampat, Papua Barat Daya',
            'latitude' => -0.5902,
            'longitude' => 130.5964,
            'boundary_geojson' => $this->polygonFeature(
                [
                    [130.495, -0.560], [130.560, -0.550], [130.620, -0.558], [130.685, -0.572],
                    [130.692, -0.598], [130.665, -0.622], [130.600, -0.635], [130.540, -0.630],
                    [130.500, -0.610], [130.490, -0.585], [130.495, -0.560],
                ],
                'Kawasan Selat Dampier',
                'Area survey valuasi ekonomi kawasan terumbu karang Selat Dampier (P. Mansuar, Kri, Arborek), Raja Ampat.'
            ),
        ]);

        $danauTobaPath = resource_path('js/data/danau-toba-boundary.json');
        if (is_file($danauTobaPath)) {
            Project::where('code', 'PROJ-002')->update([
                'boundary_geojson' => json_decode(file_get_contents($danauTobaPath), true),
            ]);
        }
    }

    // === Region configs ======================================================

    private function baliConfig(int $adminId): array
    {
        return [
            'admin_id' => $adminId,
            'code' => 'PROJ-005',
            'name' => 'Valuasi Ekonomi Kawasan Konservasi Perairan Nusa Penida',
            'description' => 'Penilaian TEV Kawasan Konservasi Perairan Nusa Penida (dari Toyapakeh di barat hingga tebing Kelingking di selatan) meliputi wisata bahari (manta ray di Manta Point, mola-mola), perikanan karang & rumput laut berkelanjutan, dan nilai keberadaan biota laut langka.',
            'location' => 'Sampalan, Nusa Penida, Klungkung, Bali',
            'province' => 'Bali',
            // Pelabuhan Sampalan, verified via OSM/Nominatim: -8.6727, 115.5543.
            'latitude' => -8.6727,
            'longitude' => 115.5543,
            // Wedge-shaped island: wide near the Toyapakeh/Crystal Bay coast
            // (west), narrowing toward Atuh Beach in the east; south edge
            // follows the Kelingking–Suwehan cliff coastline. North edge
            // pulled out slightly to comfortably contain Sampalan harbor.
            'boundary' => [
                'ring' => [
                    [115.445, -8.680], [115.480, -8.665], [115.560, -8.660], [115.605, -8.690],
                    [115.635, -8.715], [115.615, -8.750], [115.560, -8.775], [115.505, -8.768],
                    [115.472, -8.752], [115.450, -8.725], [115.445, -8.680],
                ],
                'name' => 'Kawasan Konservasi Perairan Nusa Penida',
                'description' => 'Area survey valuasi ekonomi Kawasan Konservasi Perairan Nusa Penida, dari Toyapakeh hingga tebing Kelingking, Bali.',
            ],
            'respondent_base' => ['tcm' => 3000, 'cvm' => 4000],
            'total_annual_visits' => 95000,
            'population' => 45000,
            'tcm_rows' => [
                ['distance' => 15, 'transportation_cost' => 150000, 'time_cost' => 40000, 'visits' => 10, 'origin' => 'Denpasar', 'category' => 'Lokal'],
                ['distance' => 30, 'transportation_cost' => 280000, 'time_cost' => 70000, 'visits' => 8, 'origin' => 'Denpasar', 'category' => 'Lokal'],
                ['distance' => 45, 'transportation_cost' => 400000, 'time_cost' => 100000, 'visits' => 7, 'origin' => 'Badung', 'category' => 'Lokal'],
                ['distance' => 60, 'transportation_cost' => 500000, 'time_cost' => 150000, 'visits' => 6, 'origin' => 'Gianyar', 'category' => 'Lokal'],
                ['distance' => 80, 'transportation_cost' => 700000, 'time_cost' => 200000, 'visits' => 5, 'origin' => 'Surabaya', 'category' => 'Wisatawan'],
                ['distance' => 110, 'transportation_cost' => 1000000, 'time_cost' => 300000, 'visits' => 4, 'origin' => 'Jakarta', 'category' => 'Wisatawan'],
                ['distance' => 150, 'transportation_cost' => 1400000, 'time_cost' => 400000, 'visits' => 3, 'origin' => 'Malang', 'category' => 'Wisatawan'],
                ['distance' => 200, 'transportation_cost' => 1900000, 'time_cost' => 600000, 'visits' => 2, 'origin' => 'Yogyakarta', 'category' => 'Wisatawan'],
                ['distance' => 260, 'transportation_cost' => 2400000, 'time_cost' => 800000, 'visits' => 1, 'origin' => 'Semarang', 'category' => 'Wisatawan'],
                ['distance' => 340, 'transportation_cost' => 3100000, 'time_cost' => 1000000, 'visits' => 1, 'origin' => 'Bandung', 'category' => 'Wisatawan'],
            ],
            'tourism_description' => 'Wisata bahari Nusa Penida (diving & snorkeling manta ray di Manta Point, Crystal Bay)',
            'cvm_rows' => [
                ['wtp' => 150000, 'income' => 6000000, 'location' => 'Denpasar', 'education' => 'S1'],
                ['wtp' => 120000, 'income' => 5000000, 'location' => 'Badung', 'education' => 'SMA'],
                ['wtp' => 0, 'income' => 3000000, 'location' => 'Gianyar', 'education' => 'SMA'],
                ['wtp' => 200000, 'income' => 8000000, 'location' => 'Jakarta', 'education' => 'S2'],
                ['wtp' => 90000, 'income' => 4500000, 'location' => 'Surabaya', 'education' => 'D3'],
                ['wtp' => 0, 'income' => 2800000, 'location' => 'Malang', 'education' => 'SMA'],
                ['wtp' => 250000, 'income' => 9000000, 'location' => 'Jakarta', 'education' => 'S1'],
                ['wtp' => 180000, 'income' => 7000000, 'location' => 'Yogyakarta', 'education' => 'S1'],
                ['wtp' => 60000, 'income' => 3500000, 'location' => 'Semarang', 'education' => 'D3'],
                ['wtp' => 0, 'income' => 2500000, 'location' => 'Denpasar', 'education' => 'SMA'],
                ['wtp' => 220000, 'income' => 7500000, 'location' => 'Badung', 'education' => 'S1'],
                ['wtp' => 0, 'income' => 2200000, 'location' => 'Gianyar', 'education' => 'SMA'],
            ],
            'existence_description' => 'Nilai keberadaan biota laut langka (manta ray & mola-mola) di Kawasan Konservasi Perairan Nusa Penida',
            'eop_rows' => [
                ['commodity' => 'Ikan Karang', 'before' => 600, 'after' => 780, 'unit' => 'Ton', 'price' => 32000000, 'note' => 'hasil efek tumpahan (spillover) dari zona larang tangkap KKP'],
                ['commodity' => 'Rumput Laut', 'before' => 450, 'after' => 520, 'unit' => 'Ton', 'price' => 18000000, 'note' => 'perluasan zona budidaya rumput laut berkelanjutan'],
            ],
            'manual_benefit' => [
                'category' => 'indirect_use', 'subcategory' => 'carbon_sequestration',
                'description' => 'Blue carbon padang lamun kawasan konservasi', 'value' => 18000000000,
            ],
            'costs' => [
                ['direct_cost', 'investment', 'Infrastruktur konservasi & mooring buoy', 12000000000, 'Investasi Awal'],
                ['direct_cost', 'operation_maintenance', 'Patroli & operasional kawasan konservasi', 6000000000, 'Tahunan'],
            ],
        ];
    }

    private function kalimantanSelatanConfig(int $adminId): array
    {
        return [
            'admin_id' => $adminId,
            'code' => 'PROJ-006',
            'name' => 'Valuasi Ekonomi Kawasan Hutan Pegunungan Meratus (Loksado)',
            'description' => 'Penilaian TEV kawasan hutan Pegunungan Meratus di Kecamatan Loksado meliputi ekowisata (bamboo rafting/balamban, air terjun Haratai), hasil hutan bukan kayu (rotan, madu kalulut), dan nilai keberadaan hutan adat Dayak Meratus.',
            'location' => 'Loksado, Hulu Sungai Selatan, Kalimantan Selatan',
            'province' => 'Kalimantan Selatan',
            // Loksado village, verified via OSM/Nominatim: -2.7947, 115.4960.
            'latitude' => -2.7947,
            'longitude' => 115.4960,
            // Hilly valley shape around Loksado village, roughly 40 km east
            // of Kandangan, following the Amandit river valley.
            'boundary' => [
                'ring' => [
                    [115.440, -2.740], [115.490, -2.730], [115.530, -2.750], [115.545, -2.785],
                    [115.530, -2.820], [115.495, -2.840], [115.455, -2.830], [115.425, -2.805],
                    [115.418, -2.770], [115.440, -2.740],
                ],
                'name' => 'Kawasan Hutan Pegunungan Meratus (Loksado)',
                'description' => 'Area survey valuasi ekonomi kawasan hutan Pegunungan Meratus di Kecamatan Loksado, Kalimantan Selatan.',
            ],
            'respondent_base' => ['tcm' => 5000, 'cvm' => 6000],
            'total_annual_visits' => 40000,
            'population' => 60000,
            'tcm_rows' => [
                ['distance' => 10, 'transportation_cost' => 100000, 'time_cost' => 30000, 'visits' => 6, 'origin' => 'Loksado', 'category' => 'Lokal'],
                ['distance' => 20, 'transportation_cost' => 190000, 'time_cost' => 60000, 'visits' => 5, 'origin' => 'Kandangan', 'category' => 'Lokal'],
                ['distance' => 35, 'transportation_cost' => 300000, 'time_cost' => 100000, 'visits' => 5, 'origin' => 'Barabai', 'category' => 'Lokal'],
                ['distance' => 55, 'transportation_cost' => 450000, 'time_cost' => 150000, 'visits' => 4, 'origin' => 'Banjarbaru', 'category' => 'Lokal'],
                ['distance' => 80, 'transportation_cost' => 650000, 'time_cost' => 200000, 'visits' => 3, 'origin' => 'Banjarmasin', 'category' => 'Wisatawan'],
                ['distance' => 110, 'transportation_cost' => 850000, 'time_cost' => 250000, 'visits' => 3, 'origin' => 'Martapura', 'category' => 'Wisatawan'],
                ['distance' => 150, 'transportation_cost' => 1150000, 'time_cost' => 350000, 'visits' => 2, 'origin' => 'Palangkaraya', 'category' => 'Wisatawan'],
                ['distance' => 200, 'transportation_cost' => 1550000, 'time_cost' => 450000, 'visits' => 1, 'origin' => 'Balikpapan', 'category' => 'Wisatawan'],
                ['distance' => 260, 'transportation_cost' => 2000000, 'time_cost' => 600000, 'visits' => 1, 'origin' => 'Samarinda', 'category' => 'Wisatawan'],
                ['distance' => 330, 'transportation_cost' => 2500000, 'time_cost' => 750000, 'visits' => 1, 'origin' => 'Makassar', 'category' => 'Wisatawan'],
            ],
            'tourism_description' => 'Ekowisata Loksado (bamboo rafting/balamban Sungai Amandit & air terjun Haratai)',
            'cvm_rows' => [
                ['wtp' => 80000, 'income' => 3500000, 'location' => 'Kandangan', 'education' => 'SMA'],
                ['wtp' => 60000, 'income' => 3000000, 'location' => 'Barabai', 'education' => 'SMA'],
                ['wtp' => 0, 'income' => 2200000, 'location' => 'Loksado', 'education' => 'SMA'],
                ['wtp' => 100000, 'income' => 5000000, 'location' => 'Banjarbaru', 'education' => 'S1'],
                ['wtp' => 50000, 'income' => 2800000, 'location' => 'Banjarmasin', 'education' => 'D3'],
                ['wtp' => 120000, 'income' => 6000000, 'location' => 'Martapura', 'education' => 'S1'],
                ['wtp' => 0, 'income' => 2000000, 'location' => 'Kandangan', 'education' => 'SMA'],
                ['wtp' => 90000, 'income' => 4200000, 'location' => 'Banjarbaru', 'education' => 'S1'],
                ['wtp' => 40000, 'income' => 2600000, 'location' => 'Barabai', 'education' => 'SMA'],
                ['wtp' => 0, 'income' => 1800000, 'location' => 'Loksado', 'education' => 'SMA'],
                ['wtp' => 70000, 'income' => 3200000, 'location' => 'Kandangan', 'education' => 'D3'],
                ['wtp' => 0, 'income' => 1900000, 'location' => 'Loksado', 'education' => 'SMA'],
            ],
            'existence_description' => 'Nilai keberadaan hutan adat Dayak Meratus',
            'eop_rows' => [
                ['commodity' => 'Rotan', 'before' => 1200, 'after' => 1450, 'unit' => 'Ton', 'price' => 6000000, 'note' => 'program hutan lestari berbasis masyarakat'],
                ['commodity' => 'Madu Hutan Kalulut', 'before' => 800, 'after' => 1050, 'unit' => 'Kg', 'price' => 180000, 'note' => 'budidaya lebah kalulut oleh masyarakat adat'],
            ],
            'manual_benefit' => [
                'category' => 'indirect_use', 'subcategory' => 'water_regulation',
                'description' => 'Regulasi tata air DAS Amandit dari hutan Pegunungan Meratus', 'value' => 25000000000,
            ],
            'costs' => [
                ['direct_cost', 'investment', 'Program rehabilitasi hutan & pemberdayaan masyarakat adat', 9000000000, 'Investasi Awal'],
                ['direct_cost', 'operation_maintenance', 'Pengawasan kawasan hutan lindung', 4000000000, 'Tahunan'],
            ],
        ];
    }

    private function sulawesiTenggaraConfig(int $adminId): array
    {
        return [
            'admin_id' => $adminId,
            'code' => 'PROJ-007',
            // Scoped to Wangi-Wangi Island (specifically its northern part —
            // Waha, Wanci, Longa) per the reference boundary supplied by the
            // user, NOT the entire ~1.39 million ha Wakatobi National Park.
            'name' => 'Valuasi Ekonomi Kawasan Wisata Bahari Wangi-Wangi (Wakatobi)',
            'description' => 'Penilaian TEV kawasan wisata bahari Pulau Wangi-Wangi bagian utara (Waha–Wanci–Longa), Wakatobi, meliputi wisata selam & snorkeling, perikanan karang & lobster berkelanjutan, dan nilai keberadaan terumbu karang Segitiga Terumbu Karang Dunia (Coral Triangle). Cakupan survey dibatasi pada Pulau Wangi-Wangi, bukan seluruh kawasan Taman Nasional Wakatobi.',
            'location' => 'Wanci, Wangi-Wangi, Wakatobi, Sulawesi Tenggara',
            'province' => 'Sulawesi Tenggara',
            // Pelabuhan Panggulubelo Wanci, verified via OSM/Nominatim:
            // -5.3389, 123.5335.
            'latitude' => -5.3389,
            'longitude' => 123.5335,
            // Traces the northern ~two-thirds of Wangi-Wangi Island — Waha in
            // the northwest, curving east past Matahora airport to the Longa
            // headland, then back through Wanci — stopping short of Lia and
            // Kapota to the south, matching the reference boundary supplied.
            'boundary' => [
                'ring' => [
                    [123.525, -5.205], [123.555, -5.198], [123.595, -5.215], [123.625, -5.245],
                    [123.648, -5.270], [123.638, -5.298], [123.610, -5.320], [123.580, -5.345],
                    [123.545, -5.352], [123.515, -5.340], [123.505, -5.300], [123.508, -5.260],
                    [123.516, -5.222], [123.525, -5.205],
                ],
                'name' => 'Pulau Wangi-Wangi (Waha–Wanci–Longa)',
                'description' => 'Area survey valuasi ekonomi Pulau Wangi-Wangi bagian utara (Waha–Wanci–Longa), Wakatobi — tidak mencakup seluruh Taman Nasional Wakatobi.',
            ],
            'respondent_base' => ['tcm' => 7000, 'cvm' => 8000],
            'total_annual_visits' => 28000,
            'population' => 20000,
            'tcm_rows' => [
                ['distance' => 5, 'transportation_cost' => 50000, 'time_cost' => 20000, 'visits' => 8, 'origin' => 'Wanci', 'category' => 'Lokal'],
                ['distance' => 15, 'transportation_cost' => 150000, 'time_cost' => 50000, 'visits' => 6, 'origin' => 'Waha', 'category' => 'Lokal'],
                ['distance' => 80, 'transportation_cost' => 600000, 'time_cost' => 200000, 'visits' => 6, 'origin' => 'Kendari', 'category' => 'Lokal'],
                ['distance' => 140, 'transportation_cost' => 900000, 'time_cost' => 300000, 'visits' => 5, 'origin' => 'Bau-Bau', 'category' => 'Lokal'],
                ['distance' => 220, 'transportation_cost' => 1300000, 'time_cost' => 400000, 'visits' => 4, 'origin' => 'Makassar', 'category' => 'Wisatawan'],
                ['distance' => 320, 'transportation_cost' => 1750000, 'time_cost' => 550000, 'visits' => 4, 'origin' => 'Surabaya', 'category' => 'Wisatawan'],
                ['distance' => 430, 'transportation_cost' => 2300000, 'time_cost' => 700000, 'visits' => 3, 'origin' => 'Jakarta', 'category' => 'Wisatawan'],
                ['distance' => 560, 'transportation_cost' => 2900000, 'time_cost' => 900000, 'visits' => 2, 'origin' => 'Bandung', 'category' => 'Wisatawan'],
                ['distance' => 700, 'transportation_cost' => 3600000, 'time_cost' => 1100000, 'visits' => 2, 'origin' => 'Singapura (WNA)', 'category' => 'Wisatawan'],
                ['distance' => 860, 'transportation_cost' => 4300000, 'time_cost' => 1300000, 'visits' => 1, 'origin' => 'Jerman (WNA)', 'category' => 'Wisatawan'],
            ],
            'tourism_description' => 'Wisata selam & snorkeling Pulau Wangi-Wangi (Pantai Waha, perairan sekitar Wanci & Longa)',
            'cvm_rows' => [
                ['wtp' => 200000, 'income' => 8000000, 'location' => 'Kendari', 'education' => 'S1'],
                ['wtp' => 150000, 'income' => 6000000, 'location' => 'Bau-Bau', 'education' => 'SMA'],
                ['wtp' => 0, 'income' => 3000000, 'location' => 'Wanci', 'education' => 'SMA'],
                ['wtp' => 300000, 'income' => 12000000, 'location' => 'Jakarta', 'education' => 'S2'],
                ['wtp' => 100000, 'income' => 5000000, 'location' => 'Makassar', 'education' => 'D3'],
                ['wtp' => 0, 'income' => 2500000, 'location' => 'Waha', 'education' => 'SMA'],
                ['wtp' => 250000, 'income' => 9000000, 'location' => 'Surabaya', 'education' => 'S1'],
                ['wtp' => 180000, 'income' => 7000000, 'location' => 'Bandung', 'education' => 'S1'],
                ['wtp' => 0, 'income' => 2800000, 'location' => 'Longa', 'education' => 'SMA'],
                ['wtp' => 120000, 'income' => 6500000, 'location' => 'Makassar', 'education' => 'D3'],
                ['wtp' => 90000, 'income' => 4000000, 'location' => 'Wanci', 'education' => 'SMA'],
                ['wtp' => 0, 'income' => 2400000, 'location' => 'Waha', 'education' => 'SMA'],
            ],
            'existence_description' => 'Nilai keberadaan terumbu karang Segitiga Terumbu Karang Dunia di perairan Wangi-Wangi',
            'eop_rows' => [
                ['commodity' => 'Ikan Karang', 'before' => 500, 'after' => 640, 'unit' => 'Ton', 'price' => 35000000, 'note' => 'zona pemanfaatan perikanan karang berkelanjutan'],
                ['commodity' => 'Lobster', 'before' => 25, 'after' => 34, 'unit' => 'Ton', 'price' => 350000000, 'note' => 'pemulihan stok lobster akibat pembatasan tangkap benih'],
            ],
            'manual_benefit' => [
                'category' => 'indirect_use', 'subcategory' => 'water_regulation',
                'description' => 'Perlindungan pantai Wangi-Wangi dari abrasi oleh terumbu karang', 'value' => 40000000000,
            ],
            'costs' => [
                ['direct_cost', 'investment', 'Infrastruktur dermaga & pusat konservasi terumbu karang', 15000000000, 'Investasi Awal'],
                ['direct_cost', 'operation_maintenance', 'Patroli & pengelolaan kawasan konservasi', 7000000000, 'Tahunan'],
            ],
        ];
    }

    // ── shared seeding logic ─────────────────────────────────────────────────

    private function seedRegion(array $c): void
    {
        $project = Project::updateOrCreate(
            ['code' => $c['code']],
            [
                'name' => $c['name'],
                'description' => $c['description'],
                'location' => $c['location'],
                'province' => $c['province'],
                'latitude' => $c['latitude'],
                'longitude' => $c['longitude'],
                'boundary_geojson' => $this->polygonFeature($c['boundary']['ring'], $c['boundary']['name'], $c['boundary']['description']),
                'status' => 'published',
                'created_by' => $c['admin_id'],
            ]
        );

        // Demo data: always replace so re-seeding refreshes it instead of
        // silently keeping stale rows from a previous run.
        TcmData::withTrashed()->where('project_id', $project->id)->forceDelete();
        CvmData::withTrashed()->where('project_id', $project->id)->forceDelete();
        EopData::withTrashed()->where('project_id', $project->id)->forceDelete();
        $project->benefits()->delete();
        $project->costs()->delete();

        $tcmResult = $this->recordTcmAndCalculate($project->id, $c['admin_id'], $c['respondent_base']['tcm'], $c['tcm_rows'], $c['total_annual_visits']);
        $cvmResult = $this->recordCvmAndCalculate($project->id, $c['admin_id'], $c['respondent_base']['cvm'], $c['cvm_rows'], $c['population']);

        $benefitRows = [
            ['direct_use', 'tourism', $c['tourism_description'], $tcmResult['total_cs'], 'TCM', 'tcm', count($c['tcm_rows']), $tcmResult['cs_per_visit'], $this->tcmNote($tcmResult)],
        ];

        foreach ($c['eop_rows'] as $row) {
            // Derived columns come from the calculator now that the model no
            // longer computes them on save.
            $derived = $this->calc->eopRecordValues($row['before'], $row['after'], $row['price']);
            unset($derived['impact_direction']);

            $eop = EopData::create([
                'project_id' => $project->id, 'recorded_by' => $c['admin_id'],
                'commodity_name' => $row['commodity'], 'production_before' => $row['before'], 'production_after' => $row['after'],
                'unit' => $row['unit'], 'market_price' => $row['price'], 'impact_type' => $row['after'] >= $row['before'] ? 'positive' : 'negative',
                ...$derived,
            ]);
            $eopResult = $this->calc->calculateEOP([
                'market_price' => (float) $eop->market_price,
                'q0' => (float) $eop->production_before,
                'q1' => (float) $eop->production_after,
            ]);
            $benefitRows[] = ['direct_use', 'production', "{$row['commodity']} — {$row['note']}", $eopResult['delta_nv'], 'EOP', 'eop', null, null, $this->eopNote($eop, $eopResult)];
        }

        $benefitRows[] = [
            $c['manual_benefit']['category'], $c['manual_benefit']['subcategory'], $c['manual_benefit']['description'],
            $c['manual_benefit']['value'], 'RC', 'manual', null, null, null,
        ];
        $benefitRows[] = ['non_use', 'existence_value', $c['existence_description'], $cvmResult['total_wtp'], 'CVM', 'cvm', count($c['cvm_rows']), $cvmResult['ewtp'], $this->cvmNote($cvmResult)];

        $this->seedBenefits($project->id, $c['admin_id'], $benefitRows);
        $this->seedCosts($project->id, $c['admin_id'], $c['costs']);

        $project->calculateTEV()->save();
    }

    /**
     * Persists raw TcmData rows, then feeds the same numbers into
     * EconomicValuationCalculator::calculateTCM() so the stored respondent
     * data and the computed consumer surplus always agree.
     */
    private function recordTcmAndCalculate(int $projectId, int $adminId, int $respondentIdBase, array $rows, float $totalAnnualVisits): array
    {
        $observations = [];
        foreach ($rows as $i => $row) {
            TcmData::create([
                'project_id' => $projectId,
                'recorded_by' => $adminId,
                'respondent_id' => $respondentIdBase + $i + 1,
                'distance' => $row['distance'],
                'transportation_cost' => $row['transportation_cost'],
                'time_cost' => $row['time_cost'],
                'visit_frequency' => $row['visits'],
                'origin_location' => $row['origin'],
                'respondent_category' => $row['category'],
            ]);

            $observations[] = [
                'visits' => $row['visits'],
                'travel_cost' => $row['transportation_cost'] + $row['time_cost'],
            ];
        }

        // V_i = beta0 + beta1*TC_i -> CS_per_visit = -1/beta1 -> Total_CS = CS_per_visit * total kunjungan/tahun
        return $this->calc->calculateTCM([
            'observations' => $observations,
            'total_annual_visits' => $totalAnnualVisits,
        ]);
    }

    /**
     * Persists raw CvmData rows, then feeds the same numbers into
     * EconomicValuationCalculator::calculateCVMOpenEnded().
     */
    private function recordCvmAndCalculate(int $projectId, int $adminId, int $respondentIdBase, array $rows, float $population): array
    {
        $observations = [];
        foreach ($rows as $i => $row) {
            CvmData::create([
                'project_id' => $projectId,
                'recorded_by' => $adminId,
                'respondent_id' => $respondentIdBase + $i + 1,
                'wtp' => $row['wtp'],
                'willing_to_pay' => $row['wtp'] > 0,
                'reason_if_unwilling' => $row['wtp'] > 0 ? null : 'Belum melihat manfaat langsung',
                'household_size' => random_int(2, 6),
                'household_income' => $row['income'],
                'education_level' => $row['education'],
                'respondent_location' => $row['location'],
            ]);

            $observations[] = ['wtp' => $row['wtp']];
        }

        // WTP_i = alpha0 -> EWTP = mean(WTP_i) -> Total_WTP = EWTP * populasi
        return $this->calc->calculateCVMOpenEnded([
            'observations' => $observations,
            'population' => $population,
        ]);
    }

    private function tcmNote(array $result): string
    {
        return sprintf(
            'TCM (regresi OLS): beta0=%.4f, beta1=%.8f, R2=%.4f, n=%d. CS/kunjungan=Rp%s, total kunjungan/tahun digunakan dalam kalkulasi.',
            $result['regression']['intercept'],
            $result['regression']['coefficients']['travel_cost'],
            $result['regression']['r_squared'],
            $result['regression']['n'],
            number_format($result['cs_per_visit'], 0, ',', '.')
        );
    }

    private function cvmNote(array $result): string
    {
        return sprintf(
            'CVM open-ended (regresi OLS): EWTP=Rp%s (sample mean=Rp%s), n=%d, populasi=%s.',
            number_format($result['ewtp'], 0, ',', '.'),
            number_format($result['ewtp_sample_mean'], 0, ',', '.'),
            $result['regression']['n'],
            number_format($result['population'], 0, ',', '.')
        );
    }

    private function eopNote(EopData $eop, array $result): string
    {
        return sprintf(
            'EOP (%s): Q0=%s, Q1=%s %s, Pq=Rp%s/%s, Delta_NV = Pq*(Q1-Q0) = Rp%s.',
            $eop->commodity_name,
            number_format((float) $eop->production_before, 0, ',', '.'),
            number_format((float) $eop->production_after, 0, ',', '.'),
            $eop->unit,
            number_format((float) $eop->market_price, 0, ',', '.'),
            $eop->unit,
            number_format($result['delta_nv'], 0, ',', '.')
        );
    }

    /**
     * @param  array<int, array{0: string, 1: string, 2: string, 3: float, 4: string, 5: string, 6: ?int, 7: ?float, 8: ?string}>  $rows
     *                                                                                                                                    [category, subcategory, description, value, method_used, data_source, sample_size, mean_value, calculation_notes]
     */
    private function seedBenefits(int $projectId, int $adminId, array $rows): void
    {
        foreach ($rows as [$category, $subcategory, $description, $value, $method, $source, $sampleSize, $meanValue, $notes]) {
            Benefit::create([
                'project_id' => $projectId,
                'category' => $category,
                'subcategory' => $subcategory,
                'description' => $description,
                'value' => $value,
                'method_used' => $method,
                'data_source' => $source,
                'sample_size' => $sampleSize,
                'mean_value' => $meanValue,
                'calculation_notes' => $notes,
                'calculated_by' => $adminId,
            ]);
        }
    }

    private function seedCosts(int $projectId, int $adminId, array $rows): void
    {
        foreach ($rows as [$category, $subcategory, $description, $value, $paymentType]) {
            Cost::create([
                'project_id' => $projectId,
                'category' => $category,
                'subcategory' => $subcategory,
                'description' => $description,
                'value' => $value,
                'payment_type' => $paymentType,
                'calculated_by' => $adminId,
            ]);
        }
    }

    /**
     * Wraps a closed lon/lat coordinate ring into a GeoJSON Polygon Feature.
     *
     * @param  array<int, array{0: float, 1: float}>  $ring
     */
    private function polygonFeature(array $ring, string $name, string $description): array
    {
        return [
            'type' => 'Feature',
            'properties' => ['name' => $name, 'description' => $description],
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [$ring],
            ],
        ];
    }
}
