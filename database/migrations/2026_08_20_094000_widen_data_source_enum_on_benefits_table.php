<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The original `data_source` enum only knew eop/tcm/cvm/manual/literature,
     * so a benefit pulled from DUV/HPM/ABM/CE/RCM/ADC/BTM/ecosystem-service
     * modules had no accurate value to store and silently fell back to
     * "manual" (see App\Http\Controllers\Admin\BenefitController and
     * resources/js/Pages/Admin/Benefits/BenefitForm.jsx, which now pass the
     * real module code through). Widening the column here is what makes that
     * fix actually persistable.
     *
     * Uses the fluent ->change() builder rather than raw SQL so this runs on
     * both MySQL (production) and SQLite (the test suite's in-memory DB).
     */
    private const OLD_VALUES = ['eop', 'tcm', 'cvm', 'manual', 'literature'];

    private const NEW_VALUES = ['eop', 'tcm', 'cvm', 'duv', 'hpm', 'abm', 'ce', 'rcm', 'adc', 'btm', 'ecosystem_service', 'manual', 'literature'];

    public function up(): void
    {
        Schema::table('benefits', function (Blueprint $table) {
            $table->enum('data_source', self::NEW_VALUES)->change();
        });
    }

    public function down(): void
    {
        DB::table('benefits')->whereNotIn('data_source', self::OLD_VALUES)->update(['data_source' => 'manual']);

        Schema::table('benefits', function (Blueprint $table) {
            $table->enum('data_source', self::OLD_VALUES)->change();
        });
    }
};
