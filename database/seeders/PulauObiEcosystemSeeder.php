<?php

namespace Database\Seeders;

use App\Models\EcosystemLandCover;
use App\Models\EcosystemServiceItem;
use App\Models\EcosystemValuationIndex;
use App\Models\Project;
use Illuminate\Database\Seeder;

/**
 * Seeds "Pulau Obi" (Kawasi, Obi, Halmahera Selatan, Maluku Utara) as a
 * project valued with the land-cover ecosystem-service method from "Bahan
 * Sistem Informasi VALEK" (Tabel 1 formulas: Total Nilai Ekonomi =
 * Produktivitas/ha x Harga/unit x Luas), transcribed from that document's
 * Lampiran 1-4 ("Jasa Ekosistem Indeks 1-4") — four independent valuation
 * scenarios, each covering a different subset of the site's land-cover
 * types.
 *
 * Every row's `quantity` ("Jumlah") is the value PRINTED in the source
 * table, not Produktivitas x Harga recomputed here — the two occasionally
 * differ by a small amount in the source itself (rounding baked into the
 * original spreadsheet), and transcribing the printed Jumlah is what
 * reproduces the document's own subtotals exactly. `total_value` is then
 * Jumlah x Luas, which is how the source derives "Total Nilai Ekonomi" too.
 * Rows whose source productivity (and therefore total value) is zero/"-"
 * are omitted — they don't change any subtotal, and keep this seeder to a
 * manageable size.
 *
 * KNOWN GAP — Indeks 4 (Lampiran 4): the source PDF excerpt this was
 * transcribed from has its "Luas (ha)" and "Total Nilai Ekonomi" columns
 * cut off for the Area Belukar and Area Semak Belukar tables (only
 * Produktivitas/Harga/Jumlah survived extraction). Those two land covers
 * are seeded as empty shells (area_ha null) with an explanatory note
 * instead of fabricated figures — re-run this seeder after supplying the
 * missing columns. Indeks 4's Lahan Terbangun and Cultural Services tables
 * were complete and are seeded in full; the Lahan Terbangun "Penyerapan
 * air" row also has a Jumlah in the source (85,327,875) that is far out of
 * line with the same row in Indeks 1/2 (~18,250,000) — it's transcribed
 * as printed with a note flagging the discrepancy rather than "corrected".
 */
class PulauObiEcosystemSeeder extends Seeder
{
    private const CULTURAL_ROWS = [
        // [type, item_name, productivity, unit, price]
        ['research_location', 'Pendidikan dan Penelitian', 254.00, 'orang/tahun', 154_641_952],
        ['csr', 'CSR', 1, 'Rp/tahun', 2_262_230_268],
    ];

    public function run(): void
    {
        $adminId = 1;

        $project = Project::updateOrCreate(
            ['code' => 'PROJ-008'],
            [
                'name' => 'Valuasi Ekonomi Jasa Ekosistem Pulau Obi',
                'description' => 'Valuasi Total Economic Value (TEV) jasa ekosistem per tutupan lahan (Area Reklamasi, Belukar, Semak Belukar, Lahan Terbangun, Hutan Lahan Kering Sekunder) di kawasan Kawasi, Pulau Obi — metode "Bahan Sistem Informasi VALEK" (produktivitas/ha x harga/unit x luas, dikelompokkan ke jasa Provisioning, Regulating, Supporting, dan Cultural). Empat indeks (Lampiran 1-4) merepresentasikan skenario/putaran survei terpisah, masing-masing mencakup subset tutupan lahan yang berbeda.',
                'location' => 'Kawasi, Kecamatan Obi, Halmahera Selatan, Maluku Utara',
                'province' => 'Maluku Utara',
                // Tanjung Kawasi, verified via OSM/Nominatim: -1.6175766, 127.3895399.
                'latitude' => -1.6175766,
                'longitude' => 127.3895399,
                'status' => 'published',
                'created_by' => $adminId,
            ]
        );

        EcosystemServiceItem::whereIn('valuation_index_id', $project->ecosystemValuationIndices()->pluck('id'))->delete();
        EcosystemLandCover::whereIn('valuation_index_id', $project->ecosystemValuationIndices()->pluck('id'))->delete();
        $project->ecosystemValuationIndices()->delete();

        $this->seedIndex1($project, $adminId);
        $this->seedIndex2($project, $adminId);
        $this->seedIndex3($project, $adminId);
        $this->seedIndex4($project, $adminId);
    }

    // === Indeks 1 (Lampiran 1) =================================================

    private function seedIndex1(Project $project, int $adminId): void
    {
        $index = EcosystemValuationIndex::create([
            'project_id' => $project->id,
            'index_number' => 1,
            'name' => 'Jasa Ekosistem Indeks 1',
            'notes' => 'Ditranskripsi dari Lampiran 1, "Bahan Sistem Informasi VALEK".',
            'created_by' => $adminId,
        ]);

        $this->seedLandCover($index, 'Area Reklamasi', 79.86, [
            // Provisioning — Flora (raw_material). [category, type, name, productivity, unit, price, Jumlah]
            ['provisioning', 'raw_material', 'Cemara Laut', 33.18, 'm3/ha', 3_231_311, 107_214_899],
            ['provisioning', 'raw_material', 'Sengon Laut', 23.93, 'm3/ha', 1_090_107, 26_086_261],
            ['provisioning', 'raw_material', 'Gofassa', 9.29, 'm3/ha', 4_218_039, 39_185_582],
            ['provisioning', 'raw_material', 'Akasia daun kecil', 68.68, 'm3/ha', 899_428, 61_768_218],
            ['provisioning', 'raw_material', 'Johar', 2.05, 'm3/ha', 4_200_705, 8_597_443],
            ['provisioning', 'raw_material', 'Kaliandra', 3.14, 'm3/ha', 2_098_361, 6_588_854],
            // Provisioning — Fauna (food_production)
            ['provisioning', 'food_production', 'Macropygia doreya', 1, 'Ekor/Ha', 50_000, 50_000],
            ['provisioning', 'food_production', 'Collocalia esculenta', 1, 'Ekor/Ha', 50_000, 50_000],
            ['provisioning', 'food_production', 'Aerodramus vanikorensis', 1, 'Ekor/Ha', 1_000_000, 1_000_000],
            ['provisioning', 'food_production', 'Cacomantis variolosus', 1, 'Ekor/Ha', 50_000, 50_000],
            ['provisioning', 'food_production', 'Haliastur indus', 17, 'Ekor/Ha', 880_355, 14_966_035],
            ['provisioning', 'food_production', 'Aviceda subcristata', 4, 'Ekor/Ha', 1_464_454, 5_857_814],
            ['provisioning', 'food_production', 'Icthyophaga leucogaster', 1, 'Ekor/Ha', 3_307_645, 3_307_645],
            ['provisioning', 'food_production', 'Pandion haliaetus', 1, 'Ekor/Ha', 1_464_454, 1_464_454],
            ['provisioning', 'food_production', 'Falco moluccensis', 1, 'Ekor/Ha', 611_031, 611_031],
            ['provisioning', 'food_production', 'Coracina papuensis', 4, 'Ekor/Ha', 50_000, 200_000],
            ['provisioning', 'food_production', 'Lalage aurea', 3, 'Ekor/Ha', 50_000, 150_000],
            ['provisioning', 'food_production', 'Artamus leucorynchus', 1, 'Ekor/Ha', 50_000, 50_000],
            ['provisioning', 'food_production', 'Rhipidura leucophrys', 5, 'Ekor/Ha', 50_000, 250_000],
            ['provisioning', 'food_production', 'Aplonis mysolensis', 4, 'Ekor/Ha', 50_000, 200_000],
            ['provisioning', 'food_production', 'Leptocoma aspasia', 4, 'Ekor/Ha', 50_000, 200_000],
            ['provisioning', 'food_production', 'Cinnyris frenatus', 8, 'Ekor/Ha', 50_000, 400_000],
            ['provisioning', 'food_production', 'Passer montanus', 4, 'Ekor/Ha', 5_000, 20_000],
            ['provisioning', 'food_production', 'Carlia fusca', 1, 'Ekor/Ha', 500, 500],
            ['provisioning', 'food_production', 'Bronchocela cristatella', 3, 'Ekor/Ha', 1_500, 4_500],
            ['provisioning', 'food_production', 'Acanthophis laevis', 1, 'Ekor/Ha', 7_500, 7_500],
            // Regulating
            ['regulating', 'erosion_control', 'Pencegah erosi', 153.51, 'm3/ha/th', 250_000, 38_377_500],
            ['regulating', 'water_supply', 'Penyerapan air', 21_047, 'm3/ha/th', 2_000, 42_094_712],
            ['regulating', 'litter_decomposition', 'Serasah', 1.93, 'ton/ha/th', 700_000, 539_700],
            ['regulating', 'oxygen_production', 'Penghasil Oksigen', 5.83, 'm3/ha/th', 46_400, 270_512],
            ['regulating', 'carbon_sequestration', 'Penyerapan CO2', 135.93, 'tonCO2/ha', 84_164, 11_440_202],
            // Supporting
            ['supporting', 'habitat', 'Habitat — Reptil', 5.00, 'Ekor/th', 1_200_000, 6_000_000],
            ['supporting', 'habitat', 'Habitat — Burung', 61.00, 'Ekor/th', 619_500, 37_789_500],
            ['supporting', 'nursery_ground', 'Nursery ground', 1.00, 'ha/th', 16_402_778, 16_402_778],
            ['supporting', 'soil_formation', 'Pembentukan Tanah', 1.00, 'ha/th', 1_461_600, 1_461_600],
            ['supporting', 'biodiversity', 'Biodiversitas', 1.00, 'ha/th', 114_463, 114_463],
        ]);

        $this->seedLandCover($index, 'Area Belukar', 2.58, [
            ['provisioning', 'raw_material', 'Gori', 46.58, 'm3/ha', 1_678_689, 78_193_334],
            ['regulating', 'erosion_control', 'Pencegah erosi', 160.50, 'm3/ha/th', 250_000, 40_125_000],
            ['regulating', 'water_supply', 'Penyerapan air', 19_732, 'm3/ha/th', 2_000, 39_463_793],
            ['regulating', 'litter_decomposition', 'Serasah', 1.62, 'ton/ha/th', 700_000, 453_600],
            ['regulating', 'oxygen_production', 'Penghasil Oksigen', 2.70, 'm3/ha/th', 46_400, 125_280],
            ['regulating', 'carbon_sequestration', 'Penyerapan CO2', 116.34, 'tonCO2/ha', 84_164, 9_791_640],
            ['supporting', 'nursery_ground', 'Nursery ground', 1.00, 'ha/th', 16_402_778, 16_402_778],
            ['supporting', 'soil_formation', 'Pembentukan Tanah', 1.00, 'ha/th', 1_461_600, 1_461_600],
            ['supporting', 'biodiversity', 'Biodiversitas', 1.00, 'ha/th', 114_463, 114_463],
        ]);

        $this->seedLandCover($index, 'Semak Belukar', 181.34, [
            ['provisioning', 'raw_material', 'Nani daun kecil', 8.98, 'm3/ha', 2_039_022, 18_310_418],
            ['provisioning', 'raw_material', 'Kayu pahit', 4.12, 'm3/ha', 2_039_022, 8_400_771],
            ['regulating', 'erosion_control', 'Pencegah erosi', 160.50, 'm3/ha/th', 250_000, 40_125_000],
            ['regulating', 'water_supply', 'Penyerapan air', 18_416, 'm3/ha/th', 2_000, 36_832_873],
            ['regulating', 'litter_decomposition', 'Serasah', 0.49, 'ton/ha/th', 700_000, 137_200],
            ['regulating', 'oxygen_production', 'Penghasil Oksigen', 2.70, 'm3/ha/th', 46_400, 125_280],
            ['regulating', 'carbon_sequestration', 'Penyerapan CO2', 35.10, 'tonCO2/ha', 84_164, 2_954_156],
            ['supporting', 'nursery_ground', 'Nursery ground', 1.00, 'ha/th', 16_402_778, 16_402_778],
            ['supporting', 'soil_formation', 'Pembentukan Tanah', 1.00, 'ha/th', 1_461_600, 1_461_600],
            ['supporting', 'biodiversity', 'Biodiversitas', 1.00, 'ha/th', 114_463, 114_463],
        ]);

        $this->seedLandCover($index, 'Lahan Terbangun', 88.06, [
            ['regulating', 'erosion_control', 'Pencegah erosi', 14.49, 'm3/ha/th', 250_000, 3_623_188],
            ['regulating', 'water_supply', 'Penyerapan air', 9_125, 'm3/ha/th', 2_000, 18_250_000],
        ]);

        $this->seedCulturalItems($index);
    }

    // === Indeks 2 (Lampiran 2) =================================================

    private function seedIndex2(Project $project, int $adminId): void
    {
        $index = EcosystemValuationIndex::create([
            'project_id' => $project->id,
            'index_number' => 2,
            'name' => 'Jasa Ekosistem Indeks 2',
            'notes' => 'Ditranskripsi dari Lampiran 2, "Bahan Sistem Informasi VALEK".',
            'created_by' => $adminId,
        ]);

        $this->seedLandCover($index, 'Area Reklamasi', 1.62, [
            ['provisioning', 'raw_material', 'Cemara Laut', 33.18, 'm3/ha', 3_231_311, 107_214_899],
            ['provisioning', 'raw_material', 'Sengon Laut', 23.93, 'm3/ha', 1_090_107, 26_086_261],
            ['provisioning', 'raw_material', 'Gofassa', 9.29, 'm3/ha', 4_218_039, 39_185_582],
            ['provisioning', 'raw_material', 'Akasia daun kecil', 68.68, 'm3/ha', 899_428, 61_768_218],
            ['provisioning', 'raw_material', 'Johar', 2.05, 'm3/ha', 4_200_705, 8_597_443],
            ['provisioning', 'raw_material', 'Kaliandra', 3.14, 'm3/ha', 2_098_361, 6_588_854],
            ['regulating', 'erosion_control', 'Pencegah erosi', 153.51, 'm3/ha/th', 250_000, 38_377_500],
            ['regulating', 'water_supply', 'Penyerapan air', 21_047, 'm3/ha/th', 2_000, 42_094_712],
            ['regulating', 'litter_decomposition', 'Serasah', 1.93, 'ton/ha/th', 700_000, 539_700],
            ['regulating', 'oxygen_production', 'Penghasil Oksigen', 5.83, 'm3/ha/th', 46_400, 270_512],
            ['regulating', 'carbon_sequestration', 'Penyerapan CO2', 135.93, 'tonCO2/ha', 84_164, 11_440_202],
            ['supporting', 'habitat', 'Habitat — Reptil', 5.00, 'Ekor/Tahun', 1_200_000, 6_000_000],
            ['supporting', 'habitat', 'Habitat — Burung', 61.00, 'Ekor/Tahun', 619_500, 37_789_500],
            ['supporting', 'nursery_ground', 'Nursery ground', 1.00, 'ha/tahun', 16_402_778, 16_402_778],
            ['supporting', 'soil_formation', 'Pembentukan Tanah', 1.00, 'ha/tahun', 1_461_600, 1_461_600],
            ['supporting', 'biodiversity', 'Biodiversitas', 1.00, 'ha/tahun', 114_463, 114_463],
        ]);

        $this->seedLandCover($index, 'Area Belukar', 1, [
            ['provisioning', 'raw_material', 'Gori', 46.58, 'm3/ha', 1_678_689, 78_193_334],
            ['regulating', 'erosion_control', 'Pencegah erosi', 160.50, 'ton/ha/th', 250_000, 40_125_000],
            ['regulating', 'water_supply', 'Penyerapan air', 19_732, 'm3/ha/th', 2_000, 39_463_793],
            ['regulating', 'litter_decomposition', 'Serasah', 1.62, 'ton/ha/th', 700_000, 453_600],
            ['regulating', 'oxygen_production', 'Penghasil Oksigen', 2.70, 'm3/ha/th', 46_400, 125_280],
            ['regulating', 'carbon_sequestration', 'Penyerapan CO2', 116.34, 'tonCO2/ha', 84_164, 9_791_640],
            ['supporting', 'nursery_ground', 'Nursery ground (pembibitan)', 1.00, 'ha/th', 16_402_778, 16_402_778],
            ['supporting', 'soil_formation', 'Pembentukan Tanah', 1.00, 'ha/tah', 1_461_600, 1_461_600],
            ['supporting', 'biodiversity', 'Biodiversitas', 1.00, 'ha/th', 114_463, 114_463],
        ]);

        $this->seedLandCover($index, 'Semak Belukar', 157.58, [
            ['provisioning', 'raw_material', 'Nani daun halus', 8.98, 'm3/ha', 2_039_022, 18_310_418],
            ['provisioning', 'raw_material', 'Kayu pahit', 4.12, 'm3/ha', 2_039_022, 8_400_771],
            ['provisioning', 'food_production', 'Sus scrofa', 4, 'Ekor/Ha', 250_000, 1_000_000],
            ['provisioning', 'food_production', 'Macropygia doreya', 3, 'Ekor/Ha', 50_000, 150_000],
            ['provisioning', 'food_production', 'Collocalia esculenta', 1, 'Ekor/Ha', 50_000, 50_000],
            ['provisioning', 'food_production', 'Haliastur indus', 3, 'Ekor/Ha', 880_355, 2_641_065],
            ['provisioning', 'food_production', 'Falco moluccensis', 1, 'Ekor/Ha', 611_031, 611_031],
            ['provisioning', 'food_production', 'Coracina papuensis', 1, 'Ekor/Ha', 50_000, 50_000],
            ['provisioning', 'food_production', 'Edolisoma ceramense', 1, 'Ekor/Ha', 50_000, 50_000],
            ['provisioning', 'food_production', 'Rhipidura leucophrys', 1, 'Ekor/Ha', 50_000, 50_000],
            ['provisioning', 'food_production', 'Leptocoma aspasia', 1, 'Ekor/Ha', 50_000, 50_000],
            ['provisioning', 'food_production', 'Myiagra galeata', 1, 'Ekor/Ha', 50_000, 50_000],
            ['regulating', 'erosion_control', 'Pencegah erosi', 160.50, 'm3/ha/th', 250_000, 40_125_000],
            ['regulating', 'water_supply', 'Penyerapan air', 18_416, 'm3/ha/th', 2_000, 36_832_873],
            ['regulating', 'litter_decomposition', 'Serasah', 0.49, 'ton/ha/th', 700_000, 137_200],
            ['regulating', 'oxygen_production', 'Penghasil Oksigen', 2.70, 'tonO2/ha/th', 46_400, 125_280],
            ['regulating', 'carbon_sequestration', 'Penyerapan CO2', 35.10, 'tonCO2/ha', 84_164, 2_954_156],
            ['supporting', 'habitat', 'Habitat — Burung', 13.00, 'Ekor/th', 619_500, 8_053_500],
            ['supporting', 'habitat', 'Habitat — Mamalia', 4.00, 'Ekor/th', 625_011, 2_500_044],
            ['supporting', 'nursery_ground', 'Nursery ground', 1.00, 'ha/th', 16_402_778, 16_402_778],
            ['supporting', 'soil_formation', 'Pembentukan Tanah', 1.00, 'ha/th', 1_461_600, 1_461_600],
            ['supporting', 'biodiversity', 'Biodiversitas', 1.00, 'ha/th', 114_463, 114_463],
        ]);

        $this->seedLandCover($index, 'Lahan Terbangun', 23.45, [
            ['regulating', 'erosion_control', 'Pencegah erosi', 14.49, 'm3/ha/th', 250_000, 3_623_188],
            ['regulating', 'water_supply', 'Penyerapan air', 9_125, 'm3/ha/th', 2_000, 18_250_000],
        ]);

        $this->seedCulturalItems($index);
    }

    // === Indeks 3 (Lampiran 3) =================================================

    private function seedIndex3(Project $project, int $adminId): void
    {
        $index = EcosystemValuationIndex::create([
            'project_id' => $project->id,
            'index_number' => 3,
            'name' => 'Jasa Ekosistem Indeks 3',
            'notes' => 'Ditranskripsi dari Lampiran 3, "Bahan Sistem Informasi VALEK".',
            'created_by' => $adminId,
        ]);

        $this->seedLandCover($index, 'Area Belukar', 61.13, [
            ['provisioning', 'raw_material', 'Gori', 46.58, 'm3/ha', 1_678_689, 78_193_334],
            ['provisioning', 'food_production', 'Sus scrofa', 2, 'Ekor/ha', 250_000, 500_000],
            ['provisioning', 'food_production', 'Aerodramus vanikorensis', 2, 'Ekor/ha', 1_000_000, 2_000_000],
            ['provisioning', 'food_production', 'Haliastur indus', 1, 'Ekor/ha', 880_335, 880_335],
            ['provisioning', 'food_production', 'Pandion haliaetus', 1, 'Ekor/ha', 1_464_454, 1_464_454],
            ['provisioning', 'food_production', 'Trichoglossus squamatus', 1, 'Ekor/ha', 50_000, 50_000],
            ['provisioning', 'food_production', 'Coracina papuensis', 1, 'Ekor/ha', 50_000, 50_000],
            ['regulating', 'erosion_control', 'Pencegah erosi', 160.50, 'ton/ha/th', 250_000, 40_125_000],
            ['regulating', 'water_supply', 'Penyerapan air', 19_732, 'm3/ha/th', 2_000, 39_463_793],
            ['regulating', 'litter_decomposition', 'Serasah', 1.62, 'ton/ha/tahun', 700_000, 453_600],
            ['regulating', 'oxygen_production', 'Penghasil Oksigen', 2.70, 'm3/ha/tahun', 46_400, 125_280],
            ['regulating', 'carbon_sequestration', 'Penyerapan CO2 (Carbon Stock)', 116.34, 'tonCO2/ha', 84_164, 9_791_640],
            ['supporting', 'habitat', 'Habitat — Burung', 6, 'Ekor/Tahun', 619_500, 3_717_000],
            ['supporting', 'habitat', 'Habitat — Mamalia', 2, 'Ekor/Tahun', 625_011, 1_250_022],
            ['supporting', 'nursery_ground', 'Nursery ground (pembibitan)', 1, 'ha/tahun', 16_402_778, 16_402_778],
            ['supporting', 'soil_formation', 'Pembentukan Tanah', 1, 'ha/tahun', 1_461_600, 1_461_600],
            ['supporting', 'biodiversity', 'Biodiversitas', 1, 'ha/tahun', 114_463, 114_463],
        ]);

        $this->seedLandCover($index, 'Hutan Lahan Kering Sekunder', 24.36, [
            ['provisioning', 'raw_material', 'Bintangur', 17.14, 'm3/ha', 1_787_981, 30_645_994],
            ['provisioning', 'raw_material', 'RC', 26.59, 'm3/ha', 2_039_022, 54_217_595],
            ['provisioning', 'raw_material', 'Jambu-jambu', 30.83, 'm3/ha', 2_039_022, 62_863_048],
            ['provisioning', 'raw_material', 'Meranti merah', 184.84, 'm3/ha', 4_846_967, 895_913_380],
            ['provisioning', 'raw_material', 'Gosale', 25.72, 'm3/ha', 3_243_454, 83_421_637],
            ['provisioning', 'raw_material', 'Mendarahan', 22.83, 'm3/ha', 2_039_022, 46_550_872],
            ['provisioning', 'raw_material', 'Nyatoh', 33.28, 'm3/ha', 2_564_632, 85_350_953],
            ['provisioning', 'raw_material', 'Ketapang', 27.96, 'm3/ha', 4_200_705, 117_451_712],
            ['provisioning', 'raw_material', 'Bangkirai', 61.85, 'm3/ha', 4_846_967, 299_784_909],
            ['provisioning', 'food_production', 'Rhipidura leucophrys', 1, 'Ekor/ha', 50_000, 50_000],
            ['provisioning', 'food_production', 'Aerodramus vanikorensis', 1, 'Ekor/ha', 1_000_000, 1_000_000],
            ['regulating', 'erosion_control', 'Pencegah erosi', 158.40, 'm3/ha/th', 250_000, 39_600_000],
            ['regulating', 'water_supply', 'Penyerapan air', 22_363, 'm3/ha/th', 2_000, 44_725_632],
            ['regulating', 'litter_decomposition', 'Serasah', 11.12, 'ton/ha/th', 700_000, 3_113_600],
            ['regulating', 'oxygen_production', 'Penghasil Oksigen', 17.60, 'tonCO2/ha/th', 46_400, 816_640],
            ['regulating', 'carbon_sequestration', 'Penyerapan CO2', 784.41, 'tonCo2/ha/th', 84_164, 66_019_083],
            ['supporting', 'habitat', 'Habitat — Burung', 2.00, 'Ekor/th', 619_500, 1_239_000],
            ['supporting', 'nursery_ground', 'Nursery ground', 1.00, 'ha/th', 16_402_778, 16_402_778],
            ['supporting', 'soil_formation', 'Pembentukan Tanah', 1.00, 'ha/th', 1_461_600, 1_461_600],
            ['supporting', 'biodiversity', 'Biodiversitas', 1.00, 'ha/th', 114_463, 114_463],
        ]);

        $this->seedLandCover($index, 'Semak Belukar', 154.96, [
            ['provisioning', 'raw_material', 'Gori', 43.87, 'm3/ha', 1_678_689, 73_644_086],
            ['provisioning', 'raw_material', 'Kayu seru', 5.81, 'm3/ha', 2_039_022, 11_846_718],
            ['provisioning', 'raw_material', 'Toki-toki', 10.83, 'm3/ha', 3_080_022, 33_356_638],
            ['provisioning', 'raw_material', 'Mangga hutan', 19.53, 'm3/ha', 3_080_022, 60_152_830],
            ['provisioning', 'food_production', 'Sus scrofa', 1, 'Ekor/Ha', 250_000, 250_000],
            ['provisioning', 'food_production', 'Rhipidura leucophrys', 4, 'Ekor/Ha', 50_000, 200_000],
            ['provisioning', 'food_production', 'Carlia fusca', 1, 'Ekor/Ha', 50_000, 50_000],
            ['regulating', 'erosion_control', 'Pencegah erosi', 160.50, 'm3/ha/th', 250_000, 40_125_000],
            ['regulating', 'water_supply', 'Penyerapan air', 18_416, 'm3/ha/th', 2_000, 36_832_873],
            ['regulating', 'litter_decomposition', 'Serasah', 3.29, 'ton/ha/tahun', 700_000, 921_200],
            ['regulating', 'oxygen_production', 'Penghasil Oksigen', 2.70, 'm3/ha/tahun', 46_400, 125_280],
            ['regulating', 'carbon_sequestration', 'Penyerapan CO2', 237.10, 'tonCO2/ha', 84_164, 19_955_284],
            ['supporting', 'habitat', 'Habitat — Reptil', 1, 'Ekor/Tahun', 1_200_000, 1_200_000],
            ['supporting', 'habitat', 'Habitat — Burung', 4, 'Ekor/Tahun', 619_500, 2_478_000],
            ['supporting', 'habitat', 'Habitat — Mamalia', 1, 'Ekor/Tahun', 625_011, 625_011],
            ['supporting', 'nursery_ground', 'Nursery ground (pembibitan)', 1, 'ha/tahun', 16_402_778, 16_402_778],
            ['supporting', 'soil_formation', 'Pembentukan Tanah', 1, 'ha/tahun', 1_461_600, 1_461_600],
            ['supporting', 'biodiversity', 'Biodiversitas', 1, 'ha/tahun', 114_463, 114_463],
        ], notes: 'Baris "Beringin halus" (produktivitas 13.77 m3/ha) diabaikan: sumber PDF mencantumkan Jumlah=0 dan Total="-" untuk baris ini meskipun produktivitas tidak nol — kemungkinan galat pada dokumen asli, ditranskripsi apa adanya (dianggap tanpa nilai).');

        $this->seedCulturalItems($index);
    }

    // === Indeks 4 (Lampiran 4) — partial, see class docblock ==================

    private function seedIndex4(Project $project, int $adminId): void
    {
        $index = EcosystemValuationIndex::create([
            'project_id' => $project->id,
            'index_number' => 4,
            'name' => 'Jasa Ekosistem Indeks 4',
            'notes' => 'Ditranskripsi dari Lampiran 4, "Bahan Sistem Informasi VALEK". Area Belukar dan Semak Belukar pada indeks ini BELUM lengkap — lihat catatan pada masing-masing tutupan lahan.',
            'created_by' => $adminId,
        ]);

        $this->seedLandCover($index, 'Area Belukar', null, [], notes: 'Data belum lengkap: kolom Luas (ha) dan Total Nilai Ekonomi terpotong pada dokumen sumber untuk tabel Area Belukar Lampiran 4. Produktivitas, satuan, dan harga per unit tersedia dan sama dengan Indeks lain, tetapi tanpa Luas totalnya tidak dapat dihitung. Perlu data tambahan dari dokumen sumber untuk melengkapi.');

        $this->seedLandCover($index, 'Semak Belukar', null, [], notes: 'Data belum lengkap: kolom Luas (ha) dan Total Nilai Ekonomi terpotong pada dokumen sumber untuk tabel Semak Belukar Lampiran 4. Perlu data tambahan dari dokumen sumber untuk melengkapi.');

        $this->seedLandCover($index, 'Lahan Terbangun', 345.29, [
            ['regulating', 'erosion_control', 'Pencegah erosi', 14.49, 'm3/ha/th', 250_000, 3_623_188],
            // Jumlah tercetak (85.327.875) tidak konsisten dengan pola
            // Produktivitas x Harga (9.125 x 2.000 = 18.250.000) yang
            // dipakai pada baris identik di Indeks 1 & 2 — kemungkinan
            // galat pada dokumen sumber. Ditranskripsi apa adanya (memakai
            // Jumlah yang tercetak) alih-alih "dikoreksi" tanpa dasar.
            // Total Nilai Ekonomi tidak tercetak untuk Indeks 4 sama
            // sekali (kolom terpotong), sehingga dihitung di sini sebagai
            // Jumlah x Luas.
            ['regulating', 'water_supply', 'Penyerapan air', 9_125, 'm3/ha/th', 2_000, 85_327_875],
        ], notes: 'Lihat catatan kode sumber (seeder) mengenai baris "Penyerapan air": Jumlah tercetak pada dokumen tidak konsisten dengan indeks lain. Total Nilai Ekonomi kedua baris dihitung (Jumlah x Luas), bukan disalin dari dokumen (kolom Total terpotong pada Indeks 4).');

        $this->seedCulturalItems($index);
    }

    // === shared seeding logic ===================================================

    /**
     * @param  array<int, array{0: string, 1: string, 2: string, 3: float|int, 4: string, 5: float|int, 6: float|int}>  $rows
     *                                                                                                                          [service_category, service_type, item_name, productivity, unit, unit_price, quantity ("Jumlah", as printed)]
     */
    private function seedLandCover(EcosystemValuationIndex $index, string $name, ?float $areaHa, array $rows, ?string $notes = null): EcosystemLandCover
    {
        $landCover = EcosystemLandCover::create([
            'valuation_index_id' => $index->id,
            'name' => $name,
            'area_ha' => $areaHa,
            'notes' => $notes,
        ]);

        foreach ($rows as $i => [$category, $type, $itemName, $productivity, $unit, $price, $quantity]) {
            EcosystemServiceItem::create([
                'valuation_index_id' => $index->id,
                'land_cover_id' => $landCover->id,
                'service_category' => $category,
                'service_type' => $type,
                'item_name' => $itemName,
                'productivity_value' => $productivity,
                'productivity_unit' => $unit,
                'unit_price' => $price,
                'quantity' => $quantity,
                'total_value' => $quantity * $areaHa,
                'sort_order' => $i,
            ]);
        }

        return $landCover;
    }

    /**
     * Site-level Cultural Services (Pendidikan dan Penelitian, CSR) — not
     * tied to a specific land-cover polygon (Luas = "-" in the source), so
     * total_value equals the printed price directly, not price x area.
     * Identical across all four indices in the source document.
     */
    private function seedCulturalItems(EcosystemValuationIndex $index): void
    {
        foreach (self::CULTURAL_ROWS as $i => [$type, $itemName, $productivity, $unit, $price]) {
            EcosystemServiceItem::create([
                'valuation_index_id' => $index->id,
                'land_cover_id' => null,
                'service_category' => 'cultural',
                'service_type' => $type,
                'item_name' => $itemName,
                'productivity_value' => $productivity,
                'productivity_unit' => $unit,
                'unit_price' => $price,
                'quantity' => $price,
                'total_value' => $price,
                'sort_order' => $i,
            ]);
        }
    }
}
