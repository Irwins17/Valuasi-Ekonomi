<?php

namespace Tests\Unit\Support;

use App\Support\BoundaryGeometryStore;
use Tests\TestCase;

class BoundaryGeometryStoreTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir().'/boundary-store-'.uniqid();
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

    public function test_it_round_trips_a_polygon(): void
    {
        $geometry = [
            'type' => 'Polygon',
            'coordinates' => [[[110.1, -7.1], [110.2, -7.1], [110.2, -7.2], [110.1, -7.1]]],
        ];

        BoundaryGeometryStore::put(3, '3324010', $geometry);

        $this->assertTrue(BoundaryGeometryStore::has(3, '3324010'));
        $this->assertSame($geometry, BoundaryGeometryStore::get(3, '3324010'));
    }

    public function test_it_round_trips_nested_multipolygon_coordinates(): void
    {
        $geometry = [
            'type' => 'MultiPolygon',
            'coordinates' => [
                [[[110.1, -7.1], [110.2, -7.1], [110.1, -7.1]]],
                [[[111.1, -8.1], [111.2, -8.1], [111.1, -8.1]]],
            ],
        ];

        BoundaryGeometryStore::put(2, '3324', $geometry);

        $this->assertSame($geometry, BoundaryGeometryStore::get(2, '3324'));
    }

    public function test_it_rounds_coordinates_to_the_configured_precision(): void
    {
        BoundaryGeometryStore::put(2, '3324', [
            'type' => 'Polygon',
            'coordinates' => [[[110.123456789, -7.987654321]]],
        ]);

        $this->assertSame(
            [[[110.12346, -7.98765]]],
            BoundaryGeometryStore::get(2, '3324')['coordinates']
        );
    }

    public function test_it_shards_long_codes_by_regency_but_keeps_short_ones_flat(): void
    {
        // Desa and kecamatan codes nest under their kabupaten/kota, so no
        // single directory ends up holding tens of thousands of files.
        $this->assertSame($this->dir.'/4/3324/3324010001.json.gz', BoundaryGeometryStore::path(4, '3324010001'));
        $this->assertSame($this->dir.'/3/3324/3324010.json.gz', BoundaryGeometryStore::path(3, '3324010'));
        $this->assertSame($this->dir.'/2/3324.json.gz', BoundaryGeometryStore::path(2, '3324'));
        $this->assertSame($this->dir.'/1/33.json.gz', BoundaryGeometryStore::path(1, '33'));
    }

    public function test_it_returns_null_for_a_code_with_no_file(): void
    {
        $this->assertFalse(BoundaryGeometryStore::has(4, '9999999999'));
        $this->assertNull(BoundaryGeometryStore::get(4, '9999999999'));
    }

    public function test_it_compresses_what_it_writes(): void
    {
        $ring = [];
        for ($i = 0; $i < 500; $i++) {
            $ring[] = [110.0 + $i / 10000, -7.0 - $i / 10000];
        }
        $geometry = ['type' => 'Polygon', 'coordinates' => [$ring]];

        $written = BoundaryGeometryStore::put(2, '3324', $geometry);

        $this->assertLessThan(strlen(json_encode($geometry)) / 2, $written);
    }
}
