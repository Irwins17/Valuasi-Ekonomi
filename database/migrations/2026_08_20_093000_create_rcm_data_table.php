<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replacement Cost Method entries (Langkah 6 — a standalone method,
     * distinct from the "Replacement Cost" label already used inside the
     * EROSION ecosystem-service module).
     *
     *   total_value  = quantity × replacement_cost_per_unit
     *   annual_value = total_value ÷ useful_life_years
     */
    public function up(): void
    {
        Schema::create('rcm_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('record_code')->comment('ID Data');
            $table->enum('service_category', ['provisioning', 'regulating', 'supporting', 'cultural'])
                ->default('regulating');
            $table->string('asset_type')->comment('Aset/infrastruktur alami yang digantikan');
            $table->string('location');

            $table->decimal('quantity', 18, 4)->default(0)->comment('Volume/luas aset yang digantikan');
            $table->string('unit')->nullable();
            $table->decimal('replacement_cost_per_unit', 18, 2)->default(0);
            $table->unsignedSmallInteger('useful_life_years')->nullable();

            $table->decimal('total_value', 18, 2)->default(0)->comment('Otomatis: quantity × biaya penggantian');
            $table->decimal('annual_value', 18, 2)->default(0)->comment('Otomatis: total_value ÷ umur manfaat');

            $table->unsignedSmallInteger('period_year')->nullable();
            $table->string('data_source')->nullable();
            $table->enum('data_status', ['draft', 'verified', 'final'])->default('draft');
            $table->text('notes')->nullable();

            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'data_status']);
            $table->unique(['project_id', 'record_code'], 'rcm_project_code_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rcm_data');
    }
};
