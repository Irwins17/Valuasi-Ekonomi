<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * Hedonic pricing observations — one property transaction per row.
         *
         * The implicit price δ cannot come from a single row; it is the
         * regression coefficient on the environmental attribute across all of
         * a project's properties. So MWTP and the aggregate value are computed
         * on the module's analysis panel, not stored per row.
         */
        Schema::create('hpm_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('property_code')->comment('ID properti / unit');
            $table->decimal('transaction_price', 18, 2)->comment('Harga transaksi / sewa');
            $table->string('property_type');
            $table->string('location');

            // Atribut struktural (S)
            $table->unsignedSmallInteger('bedrooms')->nullable();
            $table->decimal('land_area', 12, 2)->nullable()->comment('Luas tanah (m²)');
            $table->decimal('building_area', 12, 2)->nullable()->comment('Luas bangunan (m²)');
            $table->unsignedSmallInteger('building_age')->nullable()->comment('Usia bangunan (tahun)');

            // Atribut lingkungan pemukiman (N)
            $table->string('accessibility')->nullable();
            $table->string('crime_rate')->nullable();
            $table->string('school_quality')->nullable();

            // Atribut lingkungan hidup (E)
            $table->decimal('air_quality_index', 8, 2)->nullable()->comment('AQI');
            $table->string('pollutant_concentration')->nullable();
            $table->decimal('noise_level', 8, 2)->nullable()->comment('dB');
            $table->decimal('distance_green_space', 8, 2)->nullable()->comment('Jarak ke RTH/pantai (km)');

            $table->decimal('delta_env_quality', 12, 4)->nullable()->comment('ΔE — perubahan kualitas lingkungan');
            $table->unsignedBigInteger('affected_units')->nullable()->comment('M — jumlah unit terdampak');

            $table->string('data_source')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['project_id', 'property_code'], 'hpm_project_code_unique');
        });

        /**
         * Averting behaviour / defensive expenditure, one household per row.
         *
         *   Nilai averting   = (Q × P) + biaya waktu
         *   Pendapatan hilang = hari sakit × upah harian
         *   Total avoidance  = biaya defensif + biaya medis + pendapatan hilang
         *
         * All three are plain arithmetic on this row's own inputs, so unlike
         * HPM they are stored here and computed on save.
         */
        Schema::create('abm_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('respondent_code')->comment('ID responden / rumah tangga');
            $table->string('location');
            $table->string('risk_type')->comment('Jenis risiko / pencemaran');
            $table->string('exposure_condition')->nullable()->comment('Kondisi lingkungan / paparan');
            $table->string('defensive_action')->comment('Tindakan defensif');
            $table->string('defensive_goods')->nullable()->comment('Jenis barang/jasa defensif');

            $table->decimal('quantity', 14, 4)->default(0);
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('time_cost', 18, 2)->default(0)->comment('Biaya waktu pencegahan');
            $table->decimal('medical_cost', 18, 2)->default(0);
            $table->decimal('sick_days', 8, 2)->default(0)->comment('Hari sakit / tidak bekerja');
            $table->decimal('daily_wage', 18, 2)->default(0);

            $table->decimal('defensive_expenditure', 18, 2)->default(0)->comment('Otomatis: (Q × P) + biaya waktu');
            $table->decimal('lost_income', 18, 2)->default(0)->comment('Otomatis: hari sakit × upah harian');
            $table->decimal('total_avoidance', 18, 2)->default(0)->comment('Otomatis: defensif + medis + pendapatan hilang');

            $table->unsignedSmallInteger('household_size')->nullable();
            $table->unsignedBigInteger('affected_population')->nullable();
            $table->string('data_source')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['project_id', 'respondent_code'], 'abm_project_code_unique');
        });

        /**
         * Choice experiment responses — one choice occasion per row.
         *
         * MWTPk = −βk / βp needs a conditional logit fitted over the whole
         * design, which this app does not estimate; the coefficients are
         * entered from whatever software fitted the model, and the module
         * computes MWTP and CV from them. The raw choices live here so the
         * design and responses stay recorded either way.
         */
        Schema::create('ce_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('respondent_code');
            $table->string('location')->nullable();
            $table->unsignedSmallInteger('age')->nullable();
            $table->string('education')->nullable();
            $table->decimal('income', 18, 2)->nullable();

            $table->string('scenario_title');
            $table->string('choice_set')->comment('Identitas choice set');
            $table->string('alternative_a')->nullable();
            $table->string('alternative_b')->nullable();
            $table->string('status_quo')->nullable();
            $table->enum('chosen_alternative', ['a', 'b', 'status_quo']);

            $table->string('attribute_1')->nullable();
            $table->string('attribute_1_level')->nullable();
            $table->string('attribute_2')->nullable();
            $table->string('attribute_2_level')->nullable();
            $table->string('attribute_3')->nullable();
            $table->string('attribute_3_level')->nullable();
            $table->decimal('cost_attribute', 18, 2)->nullable()->comment('Biaya / pajak / retribusi');

            $table->string('data_source')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'choice_set']);
            $table->unique(['project_id', 'respondent_code', 'choice_set'], 'ce_project_resp_set_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ce_data');
        Schema::dropIfExists('abm_data');
        Schema::dropIfExists('hpm_data');
    }
};
