<?php

namespace Tests\Feature\Admin;

use App\Console\Commands\DerivePapuaProvinces;
use App\Models\AdministrativeBoundary;
use App\Support\BoundaryGeometryStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The six post-2022 Papua provinces are derived from regency polygons rather
 * than imported, so the composition table is the only thing standing between
 * a correct map and a province with a hole in it.
 */
class PapuaProvinceCompositionTest extends TestCase
{
    use RefreshDatabase;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir().'/papua-'.uniqid();
        config(['boundaries.geometry_path' => $this->dir]);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->dir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->dir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
            rmdir($this->dir);
        }

        parent::tearDown();
    }

    private function composition(): array
    {
        $r = new \ReflectionClass(DerivePapuaProvinces::class);

        return $r->getConstant('COMPOSITION');
    }

    /**
     * Seeds one square-degree regency per code so the command has something to
     * dissolve, with each square offset by its index to keep bounding boxes
     * distinguishable.
     */
    private function seedRegencies(array $codes): void
    {
        foreach (array_values($codes) as $i => $code) {
            $lat = -9 + $i * 0.5;
            $lng = 130 + $i * 0.5;

            BoundaryGeometryStore::put(2, $code, [
                'type' => 'Polygon',
                'coordinates' => [[[$lng, $lat], [$lng + 1, $lat], [$lng + 1, $lat + 1], [$lng, $lat + 1], [$lng, $lat]]],
            ]);

            AdministrativeBoundary::create([
                'level' => 2, 'code' => $code, 'name' => "Kab {$code}",
                'parent_code' => str_starts_with($code, '91') ? '91' : '94',
                'province_name' => 'Lama',
                'min_lat' => $lat, 'min_lng' => $lng, 'max_lat' => $lat + 1, 'max_lng' => $lng + 1,
            ]);
        }
    }

    private function allCodes(): array
    {
        return collect($this->composition())->flatMap(fn ($c) => $c[1])->all();
    }

    public function test_every_papua_regency_is_claimed_exactly_once(): void
    {
        $codes = $this->allCodes();

        $this->assertSame(
            count($codes),
            count(array_unique($codes)),
            'A regency appears in more than one province'
        );
        // 29 regencies in old Papua (94) plus 13 in old Papua Barat (91).
        $this->assertCount(42, $codes);
    }

    public function test_it_builds_all_six_provinces_with_official_codes(): void
    {
        $this->seedRegencies($this->allCodes());

        $this->artisan('boundaries:derive-papua-provinces')->assertSuccessful();

        $provinces = AdministrativeBoundary::where('level', 1)->orderBy('code')->pluck('name', 'code');

        $this->assertSame([
            '91' => 'Papua',
            '92' => 'Papua Barat',
            '93' => 'Papua Selatan',
            '94' => 'Papua Tengah',
            '95' => 'Papua Pegunungan',
            '96' => 'Papua Barat Daya',
        ], $provinces->all());
    }

    public function test_each_province_geometry_holds_every_one_of_its_regencies(): void
    {
        $this->seedRegencies($this->allCodes());
        $this->artisan('boundaries:derive-papua-provinces')->assertSuccessful();

        foreach ($this->composition() as $code => [$name, $regencyCodes]) {
            $geometry = BoundaryGeometryStore::get(1, (string) $code);

            $this->assertSame('MultiPolygon', $geometry['type'], "{$name} is not a MultiPolygon");
            $this->assertCount(
                count($regencyCodes),
                $geometry['coordinates'],
                "{$name} should carry one polygon per regency"
            );
        }
    }

    public function test_regencies_are_repointed_at_their_new_province(): void
    {
        $this->seedRegencies($this->allCodes());
        $this->artisan('boundaries:derive-papua-provinces')->assertSuccessful();

        // Kota Sorong moved from Papua Barat to Papua Barat Daya in 2022.
        $sorong = AdministrativeBoundary::where('level', 2)->where('code', '9171')->first();
        $this->assertSame('96', $sorong->parent_code);
        $this->assertSame('Papua Barat Daya', $sorong->province_name);

        // Merauke moved from Papua to Papua Selatan.
        $merauke = AdministrativeBoundary::where('level', 2)->where('code', '9401')->first();
        $this->assertSame('93', $merauke->parent_code);
        $this->assertSame('Papua Selatan', $merauke->province_name);
    }

    public function test_it_fails_loudly_when_a_regency_polygon_is_missing(): void
    {
        $codes = $this->allCodes();
        array_pop($codes);
        $this->seedRegencies($codes);

        $this->artisan('boundaries:derive-papua-provinces')->assertFailed();
    }

    public function test_it_is_idempotent(): void
    {
        $this->seedRegencies($this->allCodes());

        $this->artisan('boundaries:derive-papua-provinces')->assertSuccessful();
        $this->artisan('boundaries:derive-papua-provinces')->assertSuccessful();

        $this->assertSame(6, AdministrativeBoundary::where('level', 1)->count());
    }
}
