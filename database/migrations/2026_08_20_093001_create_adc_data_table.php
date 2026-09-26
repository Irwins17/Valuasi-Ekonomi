<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Avoided Damage Cost entries (Langkah 6 — project/kawasan scale, cost
     * based; distinct from ABM, which is household-scale defensive
     * expenditure / revealed preference).
     *
     *   avoided_cost = protected_area × damage_cost_per_unit × event_probability
     */
    public function up(): void
    {
        Schema::create('adc_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('record_code')->comment('ID Data');
            $table->enum('service_category', ['provisioning', 'regulating', 'supporting', 'cultural'])
                ->default('regulating');
            $table->string('damage_type')->comment('Jenis kerusakan potensial, mis. banjir, abrasi, kekeringan');
            $table->string('location');

            $table->decimal('protected_area', 18, 4)->default(0)->comment('Luas area terlindungi (ha)');
            $table->decimal('damage_cost_per_unit', 18, 2)->default(0)->comment('Estimasi biaya kerusakan per ha tanpa ekosistem');
            $table->decimal('event_probability', 5, 4)->default(1)->comment('Probabilitas kejadian per tahun (0–1)');

            $table->decimal('avoided_cost', 18, 2)->default(0)->comment('Otomatis: area × biaya kerusakan × probabilitas');

            $table->unsignedSmallInteger('period_year')->nullable();
            $table->string('data_source')->nullable();
            $table->enum('data_status', ['draft', 'verified', 'final'])->default('draft');
            $table->text('notes')->nullable();

            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'data_status']);
            $table->unique(['project_id', 'record_code'], 'adc_project_code_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adc_data');
    }
};
