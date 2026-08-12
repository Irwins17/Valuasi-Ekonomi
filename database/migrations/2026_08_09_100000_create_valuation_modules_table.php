<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registry of valuation modules shown on a project.
     *
     * Holds two kinds of row, both keyed by (project_id, code):
     *  - custom modules the user defines through "Tambah Modul"
     *    (is_builtin = false), and
     *  - configuration overrides for the built-in methods defined in
     *    App\Support\ValuationModuleCatalog (is_builtin = true), which only
     *    exist once someone has opened "Konfigurasi" and saved.
     *
     * A built-in without a row here still renders on the module list; the
     * catalog supplies its defaults and its status is derived from how many
     * records the underlying module table holds.
     */
    public function up(): void
    {
        Schema::create('valuation_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('code')->comment('Kode modul, unik per proyek');
            $table->string('name');
            $table->string('valuation_method')->nullable()->comment('Metode valuasi, contoh: EOP, TCM, CVM');
            $table->string('method_group')->nullable()->comment('Kelompok metode: direct_use, revealed_preference, ...');
            $table->enum('service_category', ['provisioning', 'regulating', 'supporting', 'cultural'])
                ->nullable()->comment('Cakupan jasa ekosistem');
            $table->string('subcategory')->nullable()->comment('Subkategori / tujuan penilaian');
            $table->text('formula_summary')->nullable()->comment('Rumus ringkas');
            $table->text('input_variables')->nullable()->comment('Variabel input utama');
            $table->string('output_unit')->nullable()->comment('Satuan output, contoh: Rp/tahun');
            $table->string('data_source')->nullable();
            $table->enum('status', ['aktif', 'draft'])->default('draft');
            $table->text('description')->nullable();
            $table->boolean('show_on_project_detail')->default(true);
            $table->boolean('is_builtin')->default(false)
                ->comment('true = override konfigurasi untuk modul bawaan katalog');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['project_id', 'code']);
            $table->index(['project_id', 'service_category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valuation_modules');
    }
};
