<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gives benefits and costs the two things a present value needs: the year
     * the amount belongs to, and somewhere to store the discounted figure.
     *
     * `benefits` had no year at all, which is why the "Present Value (PV)"
     * label on the project page was not yet true — every amount was being
     * summed at face value. `costs` already had `year_applied`, so that one
     * is reused rather than duplicated.
     *
     * Both new columns are nullable and additive. A row with no year is
     * treated as belonging to the base year, so its discount factor is 1 and
     * every total that exists today keeps its current value.
     */
    public function up(): void
    {
        Schema::table('benefits', function (Blueprint $table) {
            $table->unsignedSmallInteger('period_year')->nullable()->after('value')
                ->comment('Tahun nilai ini berlaku; kosong = tahun dasar');
            $table->decimal('pv_value', 18, 2)->nullable()->after('period_year')
                ->comment('Otomatis: value didiskontokan ke tahun dasar');
        });

        Schema::table('costs', function (Blueprint $table) {
            $table->decimal('pv_value', 18, 2)->nullable()->after('year_applied')
                ->comment('Otomatis: value didiskontokan ke tahun dasar');
        });
    }

    public function down(): void
    {
        Schema::table('benefits', function (Blueprint $table) {
            $table->dropColumn(['period_year', 'pv_value']);
        });

        Schema::table('costs', function (Blueprint $table) {
            $table->dropColumn('pv_value');
        });
    }
};
