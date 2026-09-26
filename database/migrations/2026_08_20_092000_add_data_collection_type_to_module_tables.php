<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data Primer vs Data Sekunder classification (flowchart Langkah 5),
     * added next to each module's existing `data_source` free-text column.
     * See App\Support\DataCollectionTypes for the fixed method lists.
     */
    private const TABLES = [
        'duv_data', 'eop_data', 'tcm_analyses', 'cvm_analyses',
        'hpm_data', 'abm_data', 'ce_data', 'ecosystem_service_records',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->enum('data_collection_type', ['primer', 'sekunder'])->nullable()->after('data_source');
                $t->string('collection_method')->nullable()->after('data_collection_type');
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['data_collection_type', 'collection_method']);
            });
        }
    }
};
