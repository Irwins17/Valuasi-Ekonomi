<?php

namespace App\Console\Commands;

use App\Models\AdministrativeBoundary;
use App\Support\BoundaryGeometryStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Rebuilds the six Papua-region provinces to their post-2022 shape.
 *
 * The HDX/BPS dataset behind boundaries:import predates the 2022 split, so it
 * ships 34 provinces: one oversized "Papua" (94) and one oversized "Papua
 * Barat" (91). The app's own province list (resources/js/lib/provinces.js) has
 * all 38, so picking e.g. "Papua Tengah" used to draw the whole of old Papua
 * and list all 29 of its regencies.
 *
 * No newer polygon dataset is needed to fix that: the new provinces are exact
 * unions of regencies we already hold, and UU 14/2022, 15/2022, 16/2022 and
 * 29/2022 fix the membership. This command dissolves the regencies into their
 * new province and renumbers the level-1 rows to the official BPS codes
 * (91 Papua, 92 Papua Barat, 93 Papua Selatan, 94 Papua Tengah,
 * 95 Papua Pegunungan, 96 Papua Barat Daya).
 *
 * It is idempotent — safe to re-run after every boundaries:import.
 */
class DerivePapuaProvinces extends Command
{
    protected $signature = 'boundaries:derive-papua-provinces';

    protected $description = 'Rebuild the six post-2022 Papua provinces from the regency polygons already imported';

    /**
     * New province code => [name, [regency codes]].
     *
     * Regency codes are the pre-split BPS codes the dataset and the emsifa
     * wilayah dropdowns both still use; only the province layer is renumbered,
     * so a regency code no longer shares its province's prefix. That is
     * deliberate: changing regency codes would break every dropdown lookup.
     */
    private const COMPOSITION = [
        '91' => ['Papua', ['9403', '9408', '9409', '9419', '9420', '9426', '9427', '9428', '9471']],
        '92' => ['Papua Barat', ['9101', '9102', '9103', '9104', '9105', '9111', '9112']],
        '93' => ['Papua Selatan', ['9401', '9413', '9414', '9415']],
        '94' => ['Papua Tengah', ['9404', '9410', '9411', '9412', '9433', '9434', '9435', '9436']],
        '95' => ['Papua Pegunungan', ['9402', '9416', '9417', '9418', '9429', '9430', '9431', '9432']],
        '96' => ['Papua Barat Daya', ['9106', '9107', '9108', '9109', '9110', '9171']],
    ];

    /** Level-1 codes the pre-split dataset used for the Papua region. */
    private const LEGACY_PROVINCE_CODES = ['91', '94'];

    public function handle(): int
    {
        ini_set('memory_limit', '1G');

        // Every regency must be accounted for exactly once, or a province
        // would silently come out with a hole in it.
        $expected = collect(self::COMPOSITION)->flatMap(fn ($c) => $c[1])->sort()->values();
        $actual = AdministrativeBoundary::where('level', 2)
            ->where(fn ($q) => $q->where('code', 'like', '91%')->orWhere('code', 'like', '94%'))
            ->orderBy('code')->pluck('code');

        if ($expected->count() !== $expected->unique()->count()) {
            $this->error('COMPOSITION lists a regency more than once.');

            return self::FAILURE;
        }

        $missing = $expected->diff($actual);
        $unassigned = $actual->diff($expected);

        if ($missing->isNotEmpty()) {
            $this->error('Regency polygons missing from the database: '.$missing->implode(', '));
            $this->line('Run boundaries:import for level 2 first.');

            return self::FAILURE;
        }
        if ($unassigned->isNotEmpty()) {
            $this->error('Regencies in the database that no province claims: '.$unassigned->implode(', '));

            return self::FAILURE;
        }

        $this->info("Verified: all {$expected->count()} Papua-region regencies are claimed exactly once.");
        $this->newLine();

        DB::transaction(function () {
            // The old rows reused codes 91 and 94 with different meanings, so
            // clear them before writing the new layer rather than upserting
            // over the top.
            AdministrativeBoundary::where('level', 1)
                ->whereIn('code', array_merge(self::LEGACY_PROVINCE_CODES, array_keys(self::COMPOSITION)))
                ->delete();

            foreach (self::COMPOSITION as $code => [$name, $regencyCodes]) {
                $this->buildProvince($code, $name, $regencyCodes);
            }
        });

        $this->newLine();
        $this->info('Provinces now in the database: '.AdministrativeBoundary::where('level', 1)->count());

        return self::SUCCESS;
    }

    private function buildProvince(string $code, string $name, array $regencyCodes): void
    {
        $polygons = [];
        $minLat = INF;
        $minLng = INF;
        $maxLat = -INF;
        $maxLng = -INF;

        $regencies = AdministrativeBoundary::where('level', 2)
            ->whereIn('code', $regencyCodes)
            ->get();

        foreach ($regencies as $regency) {
            $geometry = $regency->geojson;

            // Concatenating the rings gives a MultiPolygon covering exactly the
            // union of the regencies. Shared borders stay in the output as
            // separate rings rather than being dissolved away — harmless here,
            // since this layer is only drawn to frame and zoom to a selection.
            if ($geometry['type'] === 'MultiPolygon') {
                foreach ($geometry['coordinates'] as $polygon) {
                    $polygons[] = $polygon;
                }
            } else {
                $polygons[] = $geometry['coordinates'];
            }

            $minLat = min($minLat, $regency->min_lat);
            $minLng = min($minLng, $regency->min_lng);
            $maxLat = max($maxLat, $regency->max_lat);
            $maxLng = max($maxLng, $regency->max_lng);
        }

        $bytes = BoundaryGeometryStore::put(1, $code, [
            'type' => 'MultiPolygon',
            'coordinates' => $polygons,
        ]);

        AdministrativeBoundary::create([
            'level' => 1,
            'code' => $code,
            'name' => $name,
            'parent_code' => null,
            'province_name' => $name,
            'min_lat' => $minLat,
            'min_lng' => $minLng,
            'max_lat' => $maxLat,
            'max_lng' => $maxLng,
        ]);

        // Keep the regency layer pointing at the province that now owns it.
        AdministrativeBoundary::where('level', 2)
            ->whereIn('code', $regencyCodes)
            ->update(['parent_code' => $code, 'province_name' => $name]);

        $this->line(sprintf(
            '  %s  %-18s %2d kabupaten/kota, %3d poligon, %5.1f KB',
            $code, $name, $regencies->count(), count($polygons), $bytes / 1024
        ));
    }
}
