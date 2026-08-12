<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Completes the TCM respondent form: ticket/retribution, travel time and
     * the value of time, plus the socio-economic covariates the regression
     * needs, so total travel cost becomes
     *
     *   TC = transport + tiket + (nilai waktu × waktu tempuh)
     *
     * `time_cost` keeps its column and its meaning (the money value of travel
     * time). It simply becomes derived once both travel_time_hours and
     * time_value_per_hour are supplied; rows recorded before this migration
     * keep the figure that was entered for them, and ticket_cost defaults to
     * 0, so no existing total shifts.
     */
    public function up(): void
    {
        Schema::table('tcm_data', function (Blueprint $table) {
            $table->decimal('ticket_cost', 15, 2)->default(0)->after('transportation_cost')
                ->comment('Biaya tiket / retribusi');
            $table->decimal('travel_time_hours', 8, 2)->nullable()->after('time_cost')
                ->comment('Waktu tempuh pulang-pergi (jam)');
            $table->decimal('time_value_per_hour', 15, 2)->nullable()->after('travel_time_hours')
                ->comment('Nilai waktu per jam');
            $table->decimal('income', 15, 2)->nullable()->after('respondent_category')
                ->comment('Pendapatan responden');
            $table->unsignedSmallInteger('age')->nullable()->after('income');
            $table->string('education')->nullable()->after('age');
            $table->string('substitute_site')->nullable()->after('education')
                ->comment('Situs pengganti / alternatif wisata');
        });
    }

    public function down(): void
    {
        Schema::table('tcm_data', function (Blueprint $table) {
            $table->dropColumn([
                'ticket_cost', 'travel_time_hours', 'time_value_per_hour',
                'income', 'age', 'education', 'substitute_site',
            ]);
        });
    }
};
