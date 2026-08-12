<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets a benefit point back at the module record it came from, and gives
     * costs the activity breakdown a BCR needs to be auditable.
     *
     * `source_module` + `source_record_id` are a deliberate soft reference
     * rather than a foreign key: the target lives in one of several tables
     * (eop_data, duv_data, tcm_analyses, …), so no single constraint can
     * express it. Keeping the link at all is the point — without it a benefit
     * is a number someone typed, and nothing can tell you whether it still
     * matches the module record it was taken from.
     *
     * `value` stays the amount that feeds TEV. `annual_value` records the
     * module's own per-year figure alongside it, so a multi-year benefit does
     * not lose the annual number it was derived from.
     *
     * All columns are nullable or defaulted, so existing rows are untouched
     * and keep behaving as manual entries.
     */
    public function up(): void
    {
        Schema::table('benefits', function (Blueprint $table) {
            $table->string('source_module', 40)->nullable()->after('data_source')
                ->comment('eop, duv, tcm, cvm, abm, ecosystem_service, manual');
            $table->unsignedBigInteger('source_record_id')->nullable()->after('source_module')
                ->comment('Baris pada tabel modul asal; soft reference lintas tabel');
            $table->decimal('annual_value', 18, 2)->nullable()->after('value')
                ->comment('Nilai per tahun dari modul asal');
            $table->string('unit', 60)->nullable()->after('annual_value');
            $table->string('ecosystem_service_group', 40)->nullable()->after('subcategory')
                ->comment('provisioning, regulating, supporting, cultural');
            $table->enum('data_status', ['draft', 'verified'])->default('verified')->after('calculation_notes');

            $table->index(['project_id', 'source_module', 'source_record_id'], 'benefits_source_idx');
        });

        Schema::table('costs', function (Blueprint $table) {
            $table->string('activity_group', 40)->nullable()->after('subcategory')
                ->comment('survey, restoration, monitoring, maintenance, admin, other');
            $table->string('calculation_method')->nullable()->after('payment_type');
            $table->string('responsible_party')->nullable()->after('calculation_method');
            $table->enum('data_status', ['draft', 'verified'])->default('verified')->after('calculation_notes');
        });
    }

    public function down(): void
    {
        Schema::table('benefits', function (Blueprint $table) {
            $table->dropIndex('benefits_source_idx');
            $table->dropColumn([
                'source_module', 'source_record_id', 'annual_value',
                'unit', 'ecosystem_service_group', 'data_status',
            ]);
        });

        Schema::table('costs', function (Blueprint $table) {
            $table->dropColumn(['activity_group', 'calculation_method', 'responsible_party', 'data_status']);
        });
    }
};
