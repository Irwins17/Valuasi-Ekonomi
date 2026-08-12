<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-project valuation assumptions.
     *
     * These are the figures a report has to state alongside any present
     * value: which year money is discounted back to, at what rate, and over
     * what horizon. Holding them per project is the point — a discount rate
     * is a policy choice that differs between studies, so 6% is only the
     * starting value here, never a constant in the code.
     *
     * A project without a row behaves exactly as before: the model hands back
     * a default instance, and with no year recorded on a benefit or cost the
     * discount factor is 1, leaving nominal totals untouched.
     */
    public function up(): void
    {
        Schema::create('project_valuation_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained('projects')->onDelete('cascade');

            $table->unsignedSmallInteger('base_year')->comment('Tahun acuan diskonto (n = 0)');
            $table->decimal('discount_rate', 5, 2)->default(6.00)->comment('Persen per tahun, mis. 6.00');
            $table->unsignedSmallInteger('analysis_period')->default(10)->comment('Periode analisis (tahun)');
            $table->string('currency', 8)->default('IDR');
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedSmallInteger('end_year')->nullable();

            $table->enum('eop_value_basis', ['gross', 'net'])->default('net')
                ->comment('Nilai EOP mana yang dipakai sebagai manfaat');

            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_valuation_settings');
    }
};
