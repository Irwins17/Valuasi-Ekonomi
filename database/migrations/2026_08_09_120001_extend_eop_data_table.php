<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Brings eop_data up to the fuller EOP form: production cost, area,
     * period and provenance, plus the net value the spec asks for.
     *
     * `total_value` keeps its existing meaning (the gross ΔQ × price) so no
     * stored figure shifts underneath anyone; `net_value` is the new
     * gross − production_cost. production_cost defaults to 0, which makes
     * net_value equal total_value for every row that already exists.
     */
    public function up(): void
    {
        Schema::table('eop_data', function (Blueprint $table) {
            $table->enum('service_category', ['provisioning', 'regulating', 'supporting', 'cultural'])
                ->default('provisioning')->after('project_id');
            $table->string('product_type')->nullable()->after('commodity_name')
                ->comment('Jenis produk / jasa');
            $table->decimal('production_cost', 18, 2)->default(0)->after('market_price')
                ->comment('Biaya produksi / pengambilan');
            $table->decimal('net_value', 18, 2)->default(0)->after('total_value')
                ->comment('Otomatis: total_value − production_cost');
            $table->decimal('area_ha', 18, 4)->nullable()->after('net_value')
                ->comment('Luas area (ha)');
            $table->unsignedSmallInteger('period_year')->nullable()->after('area_ha');
            $table->string('data_source')->nullable()->after('period_year');
        });

        // Existing rows carry no production cost, so their net equals gross.
        DB::table('eop_data')->update(['net_value' => DB::raw('total_value')]);
    }

    public function down(): void
    {
        Schema::table('eop_data', function (Blueprint $table) {
            $table->dropColumn([
                'service_category', 'product_type', 'production_cost',
                'net_value', 'area_ha', 'period_year', 'data_source',
            ]);
        });
    }
};
