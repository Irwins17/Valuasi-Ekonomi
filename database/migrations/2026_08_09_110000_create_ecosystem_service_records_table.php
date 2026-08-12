<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data entry for the Tabel 1 ecosystem-service formulas.
     *
     * All six services (food production, raw material, genetic resources,
     * climate/carbon, erosion control, water supply) share the same shape —
     * a per-hectare quantity multiplied by a unit price, then scaled by area:
     *
     *   value_per_ha = quantity_value × unit_price × price_conversion
     *   total_value  = value_per_ha × area_ha
     *
     * so they share one table discriminated by `service_key`. Inputs unique to
     * a single service (biomassa, faktor karbon, jenis material, …) live in
     * `extra`; the field list per service is declared in
     * App\Support\EcosystemServiceSchemas.
     */
    public function up(): void
    {
        Schema::create('ecosystem_service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('service_key', 20)->comment('FOOD, RAWMAT, GENRES, CLIMATE, EROSION, WATER');
            $table->enum('service_category', ['provisioning', 'regulating', 'supporting', 'cultural']);
            $table->string('record_code')->comment('ID Data, contoh: FP-0015');
            $table->string('location')->comment('Lokasi / ekosistem');

            $table->decimal('quantity_value', 18, 4)->default(0)
                ->comment('Driver formula per ha per tahun: FPi, RMPi, PGRi, CSi, F, WS');
            $table->string('quantity_unit')->nullable();
            $table->decimal('unit_price', 18, 2)->default(0)->comment('Pi / PWi / BPi');
            $table->decimal('price_conversion', 18, 6)->default(1)
                ->comment('Faktor penyelaras satuan kuantitas vs harga, contoh m³→liter = 1000');
            $table->decimal('area_ha', 18, 4)->nullable()->comment('Luas area (ha)');
            $table->string('output_unit')->nullable();

            $table->decimal('value_per_ha', 18, 2)->default(0)->comment('Otomatis: kuantitas × harga × konversi');
            $table->decimal('total_value', 18, 2)->default(0)->comment('Otomatis: value_per_ha × luas area');

            $table->unsignedSmallInteger('period_year')->nullable();
            $table->string('data_source')->nullable();
            $table->json('extra')->nullable()->comment('Field khusus per jenis jasa');
            $table->text('notes')->nullable();

            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'service_key']);
            // Explicit short name: the generated one exceeds MySQL's 64-char
            // identifier limit.
            $table->unique(['project_id', 'service_key', 'record_code'], 'esr_project_service_code_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecosystem_service_records');
    }
};
