<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Local store of Indonesian administrative-boundary polygons
     * (kabupaten/kota, kecamatan, kelurahan/desa).
     *
     * OpenStreetMap/Nominatim was tried first but genuinely lacks kecamatan
     * and desa boundaries across most of rural Indonesia — e.g. none of
     * Kendal's kecamatan (Boja, Weleri, Patebon, Cepiring) exist there at
     * all. Keeping a local copy also removes a slow, rate-limited external
     * dependency from an interactive dropdown.
     *
     * `code` is the official BPS/Kemendagri region code, which matches the
     * IDs used by the wilayah dropdowns exactly (4 digits = kabupaten/kota,
     * 7 = kecamatan, 10 = kelurahan/desa), so lookups are an exact indexed
     * match rather than fuzzy name matching.
     *
     * NOTE: the `geojson` column created here is dropped again by the
     * 2026_08_07 migration — the polygons live in App\Support\
     * BoundaryGeometryStore now. See that migration for why.
     */
    public function up(): void
    {
        Schema::create('administrative_boundaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('level')->comment('2=kabupaten/kota, 3=kecamatan, 4=kelurahan/desa');
            $table->string('code', 20);
            $table->string('name', 150);
            $table->string('parent_code', 20)->nullable();
            $table->string('province_name', 100)->nullable();
            $table->json('geojson');
            // Bounding box, so the map can centre/zoom without parsing the
            // full geometry server-side.
            $table->decimal('min_lat', 10, 7);
            $table->decimal('min_lng', 10, 7);
            $table->decimal('max_lat', 10, 7);
            $table->decimal('max_lng', 10, 7);
            $table->timestamps();

            $table->unique(['level', 'code']);
            $table->index('parent_code');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrative_boundaries');
    }
};
